<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function showPaymentPage(Request $request, $rideId, $tripType = 'go')
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to complete payment.');
        }

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

        /** @phpstan-var view-string $view */
        $view = 'booking.payment';
        return view($view, compact(
            'ride',
            'user',
            'tripType',
            'pricePerSeat',
            'date',
            'time',
            'bookingData'
        ));
    }

    public function processPayment(Request $request, $rideId, $tripType = 'go')
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to complete payment.');
        }

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
            if ($availableSeats <= 0 || $numberOfSeats > $availableSeats) {
                DB::rollBack();

                return redirect()->route('find.rides')->with('error', 'Not enough seats available.');
            }

            // Calculate total price
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
            $bookingReference = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));

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

    public function showQRPayment(Request $request, $rideId, $tripType = 'go')
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to complete payment.');
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        // Get booking details from session
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

        // Calculate total price
        $totalPrice = $ride->is_exclusive || ($tripType === 'return' && $ride->return_is_exclusive) ? $pricePerSeat : ($pricePerSeat * $bookingData['number_of_seats']);

        /** @phpstan-var view-string $view */
        $view = 'booking.qr-payment';
        return view($view, compact(
            'ride',
            'user',
            'tripType',
            'pricePerSeat',
            'date',
            'time',
            'bookingData',
            'totalPrice'
        ));
    }

    // API Methods
    public function apiShowPaymentPage(Request $request, $rideId, $tripType = 'go')
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to complete payment.',
                'status' => 'error',
            ], 401);
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return response()->json([
                'message' => 'Ride not found.',
                'status' => 'error',
            ], 404);
        }

        // Get booking data from request (should be passed from booking process)
        $bookingData = $request->input('booking_data');

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
                return response()->json([
                    'message' => 'No booking data found. Please select seats first.',
                    'status' => 'error',
                ], 400);
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

        return response()->json([
            'message' => 'Payment page data retrieved successfully',
            'status' => 'success',
            'data' => [
                'ride' => $ride,
                'user' => $user,
                'trip_type' => $tripType,
                'price_per_seat' => $pricePerSeat,
                'date' => $date,
                'time' => $time,
                'booking_data' => $bookingData,
            ],
        ]);
    }

    public function apiProcessPayment(Request $request, $rideId, $tripType = 'go')
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to complete payment.',
                'status' => 'error',
            ], 401);
        }

        $ride = Ride::find($rideId);
        if (! $ride) {
            return response()->json([
                'message' => 'Ride not found.',
                'status' => 'error',
            ], 404);
        }

        // Get booking data from request
        $bookingData = $request->input('booking_data');

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
                return response()->json([
                    'message' => 'No booking data found. Please select seats first.',
                    'status' => 'error',
                ], 400);
            }
        }

        // Validate payment method
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'payment_method' => 'required|in:visa,mastercard,qr',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $paymentMethod = $request->input('payment_method');

        try {
            DB::beginTransaction();

            // Lock the ride row to prevent concurrent seat overselling
            $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
            if (! $ride) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Ride not found.',
                    'status' => 'error',
                ], 404);
            }

            // Re-check seat availability after acquiring the lock
            if ($tripType === 'return' && $ride->is_two_way) {
                $availableSeats = $ride->return_available_seats;
            } else {
                $availableSeats = $ride->available_seats;
            }

            $numberOfSeats = (int) ($bookingData['number_of_seats'] ?? 0);
            if ($availableSeats <= 0 || $numberOfSeats > $availableSeats) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Not enough seats available.',
                    'status' => 'error',
                ], 409);
            }

            // Determine price and details based on trip type
            if ($tripType === 'return' && $ride->is_two_way) {
                $pricePerSeat = $ride->return_is_exclusive ? $ride->return_exclusive_price : $ride->return_price_per_person;
            } else {
                $pricePerSeat = $ride->is_exclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person;
            }

            // Calculate total price
            $totalPrice = $ride->is_exclusive || ($tripType === 'return' && $ride->return_is_exclusive) ? $pricePerSeat : ($pricePerSeat * $numberOfSeats);

            // Generate a simulated payment reference
            $paymentReference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));

            // Generate booking reference
            $bookingReference = 'HUBER-' . strtoupper(uniqid());

            // Create the booking
            $ridePurchase = new RidePurchase;
            $ridePurchase->user_id = $user->id;
            $ridePurchase->ride_id = $rideId;
            $ridePurchase->trip_type = $tripType;
            $ridePurchase->number_of_seats = $numberOfSeats;
            $ridePurchase->selected_seats = $bookingData['selected_seats'];
            $ridePurchase->passenger_names = $bookingData['passenger_names'];
            $ridePurchase->contact_phone = $bookingData['contact_phone'];
            $ridePurchase->special_requests = $bookingData['special_requests'] ?? '';
            $ridePurchase->seats_confirmed = true;
            $ridePurchase->booking_reference = $bookingReference;
            $ridePurchase->total_amount = $totalPrice;
            $ridePurchase->payment_method = $paymentMethod;
            $ridePurchase->payment_status = 'completed';
            $ridePurchase->payment_reference = $paymentReference;
            $ridePurchase->payment_date = now();

            $ridePurchase->save();

            // Decrement available seats atomically
            if ($tripType === 'return' && $ride->is_two_way) {
                $ride->decrement('return_available_seats', $numberOfSeats);
            } else {
                $ride->decrement('available_seats', $numberOfSeats);
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully!',
                'status' => 'success',
                'data' => [
                    'booking_id' => $ridePurchase->id,
                    'booking_reference' => $bookingReference,
                    'total_amount' => $totalPrice,
                    'payment_method' => $paymentMethod,
                    'ride' => $ride,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'An error occurred while processing payment.',
                'status' => 'error',
            ], 500);
        }
    }

    public function apiShowQRPayment(Request $request, $rideId, $tripType = 'go')
    {
        // Get user from token authentication
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'message' => 'Please login to complete payment.',
                'status' => 'error',
            ], 401);
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return response()->json([
                'message' => 'Ride not found.',
                'status' => 'error',
            ], 404);
        }

        // Get booking data from request
        $bookingData = $request->input('booking_data');

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
                return response()->json([
                    'message' => 'No booking data found. Please select seats first.',
                    'status' => 'error',
                ], 400);
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

        // Calculate total price
        $totalPrice = $ride->is_exclusive || ($tripType === 'return' && $ride->return_is_exclusive) ? $pricePerSeat : ($pricePerSeat * $bookingData['number_of_seats']);

        // Generate QR code data (this would typically be a payment URL or reference)
        $qrData = json_encode([
            'ride_id' => $rideId,
            'trip_type' => $tripType,
            'user_id' => $user->id,
            'total_amount' => $totalPrice,
            'booking_data' => $bookingData,
            'timestamp' => now()->timestamp,
        ]);

        return response()->json([
            'message' => 'QR payment page data retrieved successfully',
            'status' => 'success',
            'data' => [
                'ride' => $ride,
                'user' => $user,
                'trip_type' => $tripType,
                'price_per_seat' => $pricePerSeat,
                'total_amount' => $totalPrice,
                'date' => $date,
                'time' => $time,
                'booking_data' => $bookingData,
                'qr_data' => $qrData,
            ],
        ]);
    }
}
