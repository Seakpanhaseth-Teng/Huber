<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Services\BookingService;
use App\Services\RidePricingService;
use App\Services\SeatAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BookingController extends Controller
{
    protected $ridePricingService;

    protected $seatAvailabilityService;

    protected $bookingService;

    public function __construct()
    {
        $this->ridePricingService = app(RidePricingService::class);
        $this->seatAvailabilityService = app(SeatAvailabilityService::class);
        $this->bookingService = app(BookingService::class);
    }

    public function showPaymentPage(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        $pricing = $this->ridePricingService->getPricing($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return redirect()->route('find.rides')->with('error', 'Sorry, this ride is fully booked and no longer available.');
        }

        if ($pricing['is_exclusive']) {
            $bookingData = [
                'number_of_seats' => 1,
                'selected_seats' => [1],
                'passenger_names' => [$user->name],
                'passenger_details' => [
                    ['name' => $user->name, 'seat_number' => 1, 'phone' => $user->phone],
                ],
                'contact_phone' => $user->phone,
                'special_requests' => '',
            ];
            session(['pending_booking_data' => $bookingData]);

            return redirect()->route('payment.show', ['rideId' => $rideId, 'tripType' => $tripType]);
        }

        return redirect()->route('booking.seat-selection', ['rideId' => $rideId, 'tripType' => $tripType]);
    }

    public function showSeatSelection($rideId, $tripType = 'go'): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        $pricing = $this->ridePricingService->getPricingSimple($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return redirect()->route('find.rides')->with('error', 'Sorry, this ride is fully booked and no longer available.');
        }

        $bookedSeats = $this->seatAvailabilityService->getBookedSeatsSql($rideId, $tripType);

        /** @phpstan-var view-string $view */
        $view = 'booking.seat-selection';
        return view($view, compact('ride', 'user', 'tripType', 'pricing', 'bookedSeats'))
            ->with('availableSeats', $pricing['available_seats'])
            ->with('date', $pricing['date'])
            ->with('time', $pricing['time'])
            ->with('pricePerSeat', $pricing['price_per_seat']);
    }

    public function processSeatSelection(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        $request->validate([
            'number_of_seats' => 'required|integer|min:1',
            'selected_seats' => 'required|array|min:1',
            'selected_seats.*' => 'required|integer|min:1',
            'contact_phone' => 'required|string',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $numberOfSeats = (int) $request->input('number_of_seats');
        $selectedSeats = $request->input('selected_seats', []);
        $contactPhone = $request->input('contact_phone');
        $passengerNames = $request->input('passenger_names');
        $specialRequests = $request->input('special_requests');

        $pricing = $this->ridePricingService->getPricingSimple($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return redirect()->route('find.rides')->with('error', 'Sorry, this ride is fully booked and no longer available.');
        }

        if ($numberOfSeats > $pricing['available_seats']) {
            return back()->withErrors(['number_of_seats' => 'Not enough seats available.']);
        }

        if (count($selectedSeats) !== $numberOfSeats) {
            return back()->withErrors(['selected_seats' => 'Number of selected seats must match the number of seats you want to book.']);
        }

        foreach ($selectedSeats as $seatNumber) {
            if ($seatNumber < 1 || $seatNumber > $pricing['available_seats']) {
                return back()->withErrors(['selected_seats' => "Seat number {$seatNumber} is not valid. Available seats are 1 to {$pricing['available_seats']}."]);
            }
        }

        $conflictingSeats = $this->seatAvailabilityService->getConflictingSeats($rideId, $tripType, $selectedSeats);
        if (! empty($conflictingSeats)) {
            return back()->withErrors(['selected_seats' => 'Some selected seats are already booked: ' . implode(', ', $conflictingSeats)]);
        }

        $passengerNames = array_filter($passengerNames, fn ($name) => ! empty(trim($name)));
        if (count($passengerNames) !== $numberOfSeats) {
            return back()->withErrors(['passenger_names' => 'Number of passenger names must match the number of seats.']);
        }

        $passengerDetails = [];
        for ($i = 0; $i < $numberOfSeats; $i++) {
            $passengerDetails[] = [
                'name' => $passengerNames[$i],
                'seat_number' => $selectedSeats[$i],
            ];
        }

        session(['pending_booking_data' => [
            'number_of_seats' => $numberOfSeats,
            'selected_seats' => $selectedSeats,
            'contact_phone' => $contactPhone,
            'passenger_names' => $passengerNames,
            'passenger_details' => $passengerDetails,
            'special_requests' => $specialRequests,
            'date' => $pricing['date'],
            'time' => $pricing['time'],
        ]]);

        return redirect()->route('payment.show', ['rideId' => $rideId, 'tripType' => $tripType])
            ->with('success', 'Seats selected successfully! Please complete your payment.');
    }

    public function processBooking(Request $request, $rideId, $tripType = 'go'): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
        if (! $ride) {
            return redirect()->route('find.rides')->with('error', 'Ride not found.');
        }

        $request->validate([
            'number_of_seats' => 'nullable|integer|min:1',
            'contact_phone' => 'required|string',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ], [
            'passenger_names.required' => 'Please provide passenger names.',
            'passenger_names.array' => 'Passenger names must be provided.',
            'passenger_names.min' => 'At least one passenger name is required.',
            'passenger_names.*.required' => 'All passenger names are required.',
            'passenger_names.*.string' => 'Passenger names must be text.',
            'passenger_names.*.max' => 'Passenger names cannot exceed 255 characters.',
        ]);

        $numberOfSeats = $request->input('number_of_seats');
        $numberOfSeatsHidden = $request->input('number_of_seats_hidden');
        $contactPhone = $request->input('contact_phone');
        $passengerNames = $request->input('passenger_names');
        $specialRequests = $request->input('special_requests');

        Log::info('Booking validation debug', [
            'numberOfSeats' => $numberOfSeats,
            'numberOfSeatsHidden' => $numberOfSeatsHidden,
            'passengerNamesCount' => is_array($passengerNames) ? count($passengerNames) : 'not array',
        ]);

        $pricing = $this->ridePricingService->getPricing($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return redirect()->route('find.rides')->with('error', 'Sorry, this ride is fully booked and no longer available.');
        }

        if ($pricing['is_exclusive']) {
            $numberOfSeats = $numberOfSeatsHidden ?: $pricing['available_seats'];
        } elseif (! $numberOfSeats) {
            return back()->withErrors(['number_of_seats' => 'Number of seats is required for shared rides.']);
        }

        if ($numberOfSeats > $pricing['available_seats']) {
            return back()->withErrors(['number_of_seats' => 'Not enough seats available.']);
        }

        if (! is_array($passengerNames)) {
            return back()->withErrors(['passenger_names' => 'Passenger names must be provided as an array.']);
        }

        $passengerNames = array_filter($passengerNames, fn ($name) => ! empty(trim($name)));
        $requiredPassengerCount = $pricing['is_exclusive'] ? 1 : $numberOfSeats;

        if (count($passengerNames) !== $requiredPassengerCount) {
            return back()->withErrors([
                'passenger_names' => 'Number of passenger names (' . count($passengerNames) . ") must match required count ({$requiredPassengerCount}). Please fill in all passenger names.",
            ]);
        }

        $totalPrice = $this->ridePricingService->calculateTotalPrice($ride, $tripType, (int) $numberOfSeats);

        $passengerDetails = [];
        for ($i = 0; $i < (int) $numberOfSeats; $i++) {
            $passengerDetails[] = [
                'name' => $passengerNames[$i],
                'seat_number' => $i + 1,
            ];
        }

        try {
            $booking = $this->bookingService->processBookingTransaction(
                $ride, $user, $tripType, (int) $numberOfSeats, $totalPrice,
                $passengerDetails, $contactPhone, $specialRequests,
                $request->input('payment_method', 'visa'), '1234'
            );

            session(['last_booking_id' => $booking->id]);

            return redirect()->route('booking.thank-you', $booking->id)
                ->with('success', 'Booking completed successfully!');
        } catch (\Exception $e) {
            Log::error('Booking failed: ' . $e->getMessage());

            return back()->withErrors(['general' => 'An error occurred while processing your booking. Please try again.']);
        }
    }

    public function showThankYou($bookingId): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to view booking confirmation.');
        }

        $booking = RidePurchase::with(['ride.user'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return redirect()->route('find.rides')->with('error', 'Booking not found.');
        }

        /** @phpstan-var view-string $view */
        $view = 'booking.thank-you';
        return view($view, compact('booking', 'user'));
    }

    public function showConfirmation($bookingId): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to view booking confirmation.');
        }

        $booking = RidePurchase::with(['ride.user', 'ride'])
            ->where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (! $booking) {
            return redirect()->route('user.bookings')->with('error', 'Booking not found.');
        }

        /** @phpstan-var view-string $view */
        $view = 'booking.confirmation';
        return view($view, compact('booking'));
    }

    // API Methods
    public function apiFindRides(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to find rides.', 401);
        }

        try {
            $rides = Ride::with('user')
                ->where('status', 'active')
                ->where('date', '>=', now()->format('Y-m-d'))
                ->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->paginate(15);

            return $this->jsonPaginated('Rides found successfully', $rides);
        } catch (\Exception $e) {
            return $this->jsonError('An error occurred while finding rides.', 500);
        }
    }

    public function apiShowPaymentPage(Request $request, $rideId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to book a ride.', 401);
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $pricing = $this->ridePricingService->getPricing($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return $this->jsonError('Sorry, this ride is fully booked and no longer available.', 400);
        }

        return $this->jsonSuccess('Payment page data retrieved successfully', [
            'ride' => $ride,
            'user' => $user,
            'trip_type' => $tripType,
            'price_per_seat' => $pricing['price_per_seat'],
            'available_seats' => $pricing['available_seats'],
            'date' => $pricing['date'],
            'time' => $pricing['time'],
            'is_exclusive' => $pricing['is_exclusive'],
        ]);
    }

    public function apiShowSeatSelection($rideId, $tripType = 'go'): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return $this->jsonError('Please login to book a ride.', 401);
        }

        $ride = Ride::with('user')->find($rideId);
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $pricing = $this->ridePricingService->getPricingSimple($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return $this->jsonError('Sorry, this ride is fully booked and no longer available.', 400);
        }

        $bookedSeats = $this->seatAvailabilityService->getBookedSeatsEloquent($rideId, $tripType);

        return $this->jsonSuccess('Seat selection data retrieved successfully', [
            'ride' => $ride,
            'user' => $user,
            'trip_type' => $tripType,
            'available_seats' => $pricing['available_seats'],
            'date' => $pricing['date'],
            'time' => $pricing['time'],
            'price_per_seat' => $pricing['price_per_seat'],
            'booked_seats' => $bookedSeats,
        ]);
    }

    public function apiProcessSeatSelection(Request $request, $rideId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to book a ride.', 401);
        }

        $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'number_of_seats' => 'required|integer|min:1',
            'selected_seats' => 'required|array|min:1',
            'selected_seats.*' => 'required|integer|min:1',
            'contact_phone' => 'required|string',
            'passenger_names' => 'required|array|min:1',
            'passenger_names.*' => 'required|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        $numberOfSeats = (int) $request->input('number_of_seats');
        $selectedSeats = $request->input('selected_seats', []);
        $contactPhone = $request->input('contact_phone');
        $passengerNames = $request->input('passenger_names');
        $specialRequests = $request->input('special_requests');

        $pricing = $this->ridePricingService->getPricingSimple($ride, $tripType);

        if ($pricing['available_seats'] <= 0) {
            return $this->jsonError('Sorry, this ride is fully booked and no longer available.', 400);
        }

        if ($numberOfSeats > $pricing['available_seats']) {
            return $this->jsonError('Not enough seats available.', 400);
        }

        // Check seat conflicts (added to API to match web behavior)
        $conflictingSeats = $this->seatAvailabilityService->getConflictingSeats($rideId, $tripType, $selectedSeats);
        if (! empty($conflictingSeats)) {
            return $this->jsonError('Some selected seats are already booked: ' . implode(', ', $conflictingSeats), 400);
        }

        $bookingData = [
            'number_of_seats' => $numberOfSeats,
            'selected_seats' => $selectedSeats,
            'passenger_names' => $passengerNames,
            'passenger_details' => array_map(fn ($name, $seat) => [
                'name' => $name, 'seat_number' => $seat,
            ], $passengerNames, $selectedSeats),
            'contact_phone' => $contactPhone,
            'special_requests' => $specialRequests,
        ];

        return $this->jsonSuccess('Seat selection processed successfully', [
            'booking_data' => $bookingData,
            'ride' => $ride,
            'trip_type' => $tripType,
            'total_price' => $numberOfSeats * $pricing['price_per_seat'],
        ]);
    }

    public function apiProcessBooking(Request $request, $rideId, $tripType = 'go'): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to book a ride.', 401);
        }

        $ride = Ride::where('id', $rideId)->lockForUpdate()->first();
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'booking_data' => 'required|array',
            'booking_data.number_of_seats' => 'required|integer|min:1',
            'booking_data.selected_seats' => 'required|array|min:1',
            'booking_data.passenger_names' => 'required|array|min:1',
            'booking_data.contact_phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        $bookingData = $request->input('booking_data');
        $numberOfSeats = (int) $bookingData['number_of_seats'];

        $pricing = $this->ridePricingService->getPricing($ride, $tripType);

        if ($pricing['available_seats'] <= 0 || $numberOfSeats > $pricing['available_seats']) {
            return $this->jsonError('Sorry, this ride is fully booked and no longer available.', 400);
        }

        $totalPrice = $this->ridePricingService->calculateTotalPrice($ride, $tripType, $numberOfSeats);

        $passengerDetails = array_map(fn ($name, $seat) => [
            'name' => $name, 'seat_number' => $seat,
        ], $bookingData['passenger_names'], $bookingData['selected_seats']);

        try {
            $booking = $this->bookingService->processBookingTransaction(
                $ride, $user, $tripType, $numberOfSeats, $totalPrice,
                $passengerDetails, $bookingData['contact_phone'],
                $bookingData['special_requests'] ?? '',
                $request->input('payment_method', 'visa'), '1234'
            );

            return $this->jsonSuccess('Booking processed successfully', [
                'booking_id' => $booking->id,
                'ride' => $ride,
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('An error occurred while processing the booking.', 500);
        }
    }

    public function apiShowThankYou($bookingId): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return $this->jsonError('Please login to view booking details.', 401);
        }

        $booking = RidePurchase::with('ride.user')->find($bookingId);
        if (! $booking || $booking->user_id !== $user->id) {
            return $this->jsonError('Booking not found.', 404);
        }

        return $this->jsonSuccess('Thank you page data retrieved successfully', [
            'booking' => $booking,
            'ride' => $booking->ride,
        ]);
    }

    public function apiShowConfirmation($bookingId): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return $this->jsonError('Please login to view booking details.', 401);
        }

        $booking = RidePurchase::with('ride.user')->find($bookingId);
        if (! $booking || $booking->user_id !== $user->id) {
            return $this->jsonError('Booking not found.', 404);
        }

        return $this->jsonSuccess('Confirmation page data retrieved successfully', [
            'booking' => $booking,
            'ride' => $booking->ride,
        ]);
    }
}
