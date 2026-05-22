<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function showPaymentPage(Request $request, $rideId, $tripType = 'go'): View|RedirectResponse
    {
        $user = auth()->user();

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        // Get booking details from session (set during seat selection)
        $bookingData = session('pending_booking_data');

        // If no booking data and this is an exclusive ride, create default booking data
        if (! $bookingData) {
            // Check if this is an exclusive ride
            $isExclusive = ($tripType === 'return' && $ride->is_two_way) ? $ride->return_is_exclusive : $ride->is_exclusive;

            if ($isExclusive) {
                // Create default booking data for exclusive rides
                $bookingData = [
                    'number_of_seats' => 1,
                    'selected_seats' => [1],
                    'passenger_names' => [$user->name],
                    'passenger_details' => [
                        [
                            'name' => $user->name,
                            'seat_number' => 1,
                            'phone' => $user->phone,
                        ],
                    ],
                    'contact_phone' => $user->phone,
                    'special_requests' => '',
                ];
            } else {
                return redirect()->route('find.rides')->with('error', 'No booking data found. Please select seats first.');
            }
        }

        // Determine price and details based on trip type
        if ($tripType === 'return' && $ride->is_two_way) {
            $pricePerSeat = $ride->return_is_exclusive ? $ride->return_exclusive_price : $ride->return_price_per_person;
            $date = $ride->return_date;
            $time = $ride->return_time;
            $availableSeats = $ride->return_available_seats;
        } else {
            $pricePerSeat = $ride->is_exclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person;
            $date = $ride->date;
            $time = $ride->time;
            $availableSeats = $ride->available_seats;
        }

        return view('booking.payment', compact(
            'ride',
            'user',
            'tripType',
            'pricePerSeat',
            'date',
            'time',
            'bookingData'
        ));
    }

    public function processPayment(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = auth()->user();

        $ride = Ride::find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        // Get booking data from session
        $bookingData = session('pending_booking_data');

        // If no booking data and this is an exclusive ride, create default booking data
        if (! $bookingData) {
            // Check if this is an exclusive ride
            $isExclusive = ($tripType === 'return' && $ride->is_two_way) ? $ride->return_is_exclusive : $ride->is_exclusive;

            if ($isExclusive) {
                // Create default booking data for exclusive rides
                $bookingData = [
                    'number_of_seats' => 1,
                    'selected_seats' => [1],
                    'passenger_names' => [$user->name],
                    'passenger_details' => [
                        [
                            'name' => $user->name,
                            'seat_number' => 1,
                            'phone' => $user->phone,
                        ],
                    ],
                    'contact_phone' => $user->phone,
                    'special_requests' => '',
                ];
            } else {
                return redirect()->route('find.rides')->with('error', 'No booking data found. Please select seats first.');
            }
        }

        // Validate payment method
        $request->validate([
            'payment_method' => 'required|in:visa,mastercard,qr',
        ]);

        $paymentMethod = $request->input('payment_method');

        // Re-validate booking data integrity
        if (! isset($bookingData['selected_seats']) || ! isset($bookingData['passenger_details'])) {
            return redirect()->route('find.rides')->with('error', 'Invalid booking data. Please start your booking again.');
        }

        // Prevent double-booking: check user hasn't already booked this ride
        $existingBooking = RidePurchase::where('ride_id', $rideId)
            ->where('user_id', $user->id)
            ->where('trip_type', $tripType)
            ->where('seats_confirmed', true)
            ->exists();
        if ($existingBooking) {
            return redirect()->route('user.bookings')->with('error', 'You have already booked this ride.');
        }

        try {
            DB::beginTransaction();

            // Lock the ride row to prevent concurrent seat overselling
            $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
            if (! $ride) {
                DB::rollBack();

                return redirect()->route('find.rides')->with('error', 'Ride not found.');
            }

            // Determine price and details based on trip type
            if ($tripType === 'return' && $ride->is_two_way) {
                $pricePerSeat = $ride->return_is_exclusive ? $ride->return_exclusive_price : $ride->return_price_per_person;
                $date = $ride->return_date;
                $time = $ride->return_time;
                $availableSeats = $ride->return_available_seats;
            } else {
                $pricePerSeat = $ride->is_exclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person;
                $date = $ride->date;
                $time = $ride->time;
                $availableSeats = $ride->available_seats;
            }

            // Re-check seat availability after acquiring the lock
            $numberOfSeats = (int) ($bookingData['number_of_seats'] ?? 0);
            if ($numberOfSeats < 1 || $availableSeats <= 0 || $numberOfSeats > $availableSeats) {
                DB::rollBack();

                return redirect()->route('find.rides')->with('error', 'Not enough seats available.');
            }

            // Re-check no double-booking inside the transaction (TOCTOU guard)
            $alreadyBooked = RidePurchase::where('ride_id', $rideId)
                ->where('user_id', $user->id)
                ->where('trip_type', $tripType)
                ->where('seats_confirmed', true)
                ->exists();
            if ($alreadyBooked) {
                DB::rollBack();
                return redirect()->route('user.bookings')->with('error', 'You have already booked this ride.');
            }

            // Re-check that selected seats have not been taken since the session was created
            $bookedSeats = RidePurchase::where('ride_id', $rideId)
                ->where('trip_type', $tripType)
                ->where('seats_confirmed', true)
                ->pluck('selected_seats')
                ->flatten()
                ->filter()
                ->toArray();
            $conflictingSeats = array_intersect($bookingData['selected_seats'], $bookedSeats);
            if (! empty($conflictingSeats)) {
                DB::rollBack();
                return redirect()->route('find.rides')->with('error', 'Some selected seats are no longer available. Please start your booking again.');
            }

            // Validate passenger_details count matches number_of_seats
            if (count($bookingData['passenger_details']) !== $numberOfSeats) {
                DB::rollBack();
                return redirect()->route('find.rides')->with('error', 'Booking data is inconsistent. Please start your booking again.');
            }

            // Calculate total price (derived from DB, not session — safe)
            $totalPrice = $ride->is_exclusive || ($tripType === 'return' && $ride->return_is_exclusive) ? $pricePerSeat : ($pricePerSeat * $numberOfSeats);

            // Generate a simulated payment reference
            $paymentReference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));

            // Prepare payment data
            $paymentData = [
                'payment_method' => $paymentMethod,
                'payment_status' => 'completed',
                'payment_reference' => $paymentReference,
            ];

            // Generate booking reference
            $bookingReference = 'BK' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            // Create the booking
            $booking = RidePurchase::create(array_merge([
                'ride_id' => $rideId,
                'user_id' => $user->id,
                'number_of_seats' => $numberOfSeats,
                'total_price' => $totalPrice,
                'special_requests' => $bookingData['special_requests'],
                'trip_type' => $tripType,
                'passenger_details' => $bookingData['passenger_details'],
                'selected_seats' => $bookingData['selected_seats'],
                'seats_confirmed' => true,
                'contact_phone' => $bookingData['contact_phone'],
                'booking_reference' => $bookingReference,
                'booking_date' => $date,
                'booking_time' => $time,
            ], $paymentData));

            // Decrement available seats atomically
            if ($tripType === 'return' && $ride->is_two_way) {
                $ride->decrement('return_available_seats', $numberOfSeats);
            } else {
                $ride->decrement('available_seats', $numberOfSeats);
            }

            // Send booking receipt email
            try {
                \Mail::to($user->email)->send(new \App\Mail\BookingReceipt($booking, $user));
            } catch (\Exception $e) {
                \Log::error('Failed to send booking receipt email: ' . $e->getMessage());
            }

            DB::commit();

            // Clear pending booking data from session
            session()->forget('pending_booking_data');

            // Store booking ID in session for thank you page
            session(['last_booking_id' => $booking->id]);

            return redirect()->route('booking.thank-you', $booking->id)
                ->with('success', 'Payment completed successfully! Your booking is confirmed.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());

            return back()->withErrors(['general' => 'An error occurred while processing your payment. Please try again.']);
        }
    }

    public function showQRPayment(Request $request, $rideId, $tripType = 'go'): View|RedirectResponse
    {
        $user = auth()->user();

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        // Get booking data from session
        $bookingData = session('pending_booking_data');

        // If no booking data and this is an exclusive ride, create default booking data
        if (! $bookingData) {
            // Check if this is an exclusive ride
            $isExclusive = ($tripType === 'return' && $ride->is_two_way) ? $ride->return_is_exclusive : $ride->is_exclusive;

            if ($isExclusive) {
                // Create default booking data for exclusive rides
                $bookingData = [
                    'number_of_seats' => 1,
                    'selected_seats' => [1],
                    'passenger_names' => [$user->name],
                    'passenger_details' => [
                        [
                            'name' => $user->name,
                            'seat_number' => 1,
                            'phone' => $user->phone,
                        ],
                    ],
                    'contact_phone' => $user->phone,
                    'special_requests' => '',
                ];
            } else {
                return redirect()->route('find.rides')->with('error', 'No booking data found. Please select seats first.');
            }
        }

        // Determine price and details based on trip type
        if ($tripType === 'return' && $ride->is_two_way) {
            $pricePerSeat = $ride->return_is_exclusive ? $ride->return_exclusive_price : $ride->return_price_per_person;
            $date = $ride->return_date;
            $time = $ride->return_time;
        } else {
            $pricePerSeat = $ride->is_exclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person;
            $date = $ride->date;
            $time = $ride->time;
        }

        $totalPrice = $ride->is_exclusive || ($tripType === 'return' && $ride->return_is_exclusive) ? $pricePerSeat : ($pricePerSeat * $bookingData['number_of_seats']);

        return view('booking.qr-payment', compact(
            'ride',
            'user',
            'tripType',
            'totalPrice',
            'date',
            'time',
            'bookingData'
        ));
    }
}
