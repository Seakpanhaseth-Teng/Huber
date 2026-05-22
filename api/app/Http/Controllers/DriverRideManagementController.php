<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Services\RidePricingService;
use App\Services\RideValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverRideManagementController extends Controller
{
    protected $ridePricingService;

    protected $rideValidationService;

    public function __construct()
    {
        $this->ridePricingService = app(RidePricingService::class);
        $this->rideValidationService = app(RideValidationService::class);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $allRides = Ride::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        $goRides = collect();
        $returnRides = collect();

        foreach ($allRides as $ride) {
            $goRides->push($ride);
            if ($ride->is_two_way && $ride->return_date && $ride->return_time) {
                $returnRides->push($ride);
            }
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.index';
        return view($view, compact('user', 'goRides', 'returnRides'));
    }

    public function create(): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.create';
        return view($view, compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $validated = $request->validate($this->rideValidationService->rideRules());

        $this->rideValidationService->validateRidePricing($request, $validated);
        $this->rideValidationService->validateReturnPricing($request, $validated);

        $validated['return_destination'] = $validated['station_location'];
        $ride = new Ride($validated);
        $ride->user_id = $user->id;
        $ride->save();

        return redirect()->route('driver.ride.management')->with('success', 'Ride created successfully!');
    }

    public function myRides(Request $request): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.my-rides';
        return view($view, compact('user'));
    }

    public function edit($rideId): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return redirect()->route('driver.my-rides')->with('error', 'Ride not found or access denied.');
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.edit';
        return view($view, compact('user', 'ride'));
    }

    public function update(Request $request, $rideId): RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return redirect()->route('driver.my-rides')->with('error', 'Ride not found or access denied.');
        }

        $validated = $request->validate($this->rideValidationService->rideUpdateRules());

        $this->rideValidationService->validateRidePricing($request, $validated);

        if ($request->is_two_way) {
            $returnValidation = $request->validate([
                'return_station_location' => 'required|string|max:255',
                'return_date' => 'required|date|after_or_equal:date',
                'return_time' => 'required',
                'return_available_seats' => 'required|integer|min:1',
                'return_is_exclusive' => 'required|boolean',
                'return_price_per_person' => 'nullable|numeric|min:0',
                'return_exclusive_price' => 'nullable|numeric|min:0',
                'return_station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
                'return_destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
            ]);

            $validated = array_merge($validated, $returnValidation);

            if ($request->return_is_exclusive) {
                $request->validate([
                    'return_exclusive_price' => 'required|numeric|min:0',
                ], ['return_exclusive_price.required' => 'Return exclusive price is required for exclusive return rides.']);
                $validated['return_price_per_person'] = null;
            } else {
                $request->validate([
                    'return_price_per_person' => 'required|numeric|min:0',
                ], ['return_price_per_person.required' => 'Return price per person is required for shared return rides.']);
                $validated['return_exclusive_price'] = null;
            }

            $validated['return_destination'] = $validated['station_location'];
        } else {
            $validated['return_station_location'] = null;
            $validated['return_destination'] = null;
            $validated['return_date'] = null;
            $validated['return_time'] = null;
            $validated['return_available_seats'] = null;
            $validated['return_is_exclusive'] = null;
            $validated['return_price_per_person'] = null;
            $validated['return_exclusive_price'] = null;
            $validated['return_station_location_map_url'] = null;
            $validated['return_destination_map_url'] = null;
        }

        $ride->update($validated);

        return redirect()->route('driver.my-rides')->with('success', 'Ride updated successfully!');
    }

    public function findRides(Request $request): View
    {
        $user = auth()->user();
        $userId = $user?->id;
        $query = Ride::with(['user.driverDocuments']);

        $query->where(function ($q) {
            $q->where('go_completion_status', 'pending')
                ->orWhere('return_completion_status', 'pending');
        });

        if ($request->filled('date')) {
            $query->where('date', $request->input('date'));
        }
        if ($request->filled('price_min')) {
            $min = $request->input('price_min');
            $query->where(function ($q) use ($min) {
                $q->where('go_to_price_per_person', '>=', $min)
                    ->orWhere('go_to_exclusive_price', '>=', $min)
                    ->orWhere('return_price_per_person', '>=', $min)
                    ->orWhere('return_exclusive_price', '>=', $min);
            });
        }
        if ($request->filled('price_max')) {
            $max = $request->input('price_max');
            $query->where(function ($q) use ($max) {
                $q->where('go_to_price_per_person', '<=', $max)
                    ->orWhere('go_to_exclusive_price', '<=', $max)
                    ->orWhere('return_price_per_person', '<=', $max)
                    ->orWhere('return_exclusive_price', '<=', $max);
            });
        }
        if ($request->filled('departure_time')) {
            $time = $request->input('departure_time');
            if ($time === 'morning') {
                $query->whereBetween('time', ['05:00:00', '11:59:59']);
            } elseif ($time === 'afternoon') {
                $query->whereBetween('time', ['12:00:00', '17:59:59']);
            } elseif ($time === 'evening') {
                $query->whereBetween('time', ['18:00:00', '23:59:59']);
            }
        }

        if ($request->filled('rideType')) {
            if ($request->input('rideType') === 'exclusive') {
                $query->where('is_exclusive', true);
            } elseif ($request->input('rideType') === 'shared') {
                $query->where('is_exclusive', false);
            }
        }

        if ($request->filled('from')) {
            $query->where('station_location', 'like', '%' . $request->input('from') . '%');
        }
        if ($request->filled('to')) {
            $query->where('destination', 'like', '%' . $request->input('to') . '%');
        }

        if ($request->input('sort_by') === 'price_asc') {
            $query->orderBy('go_to_price_per_person', 'asc');
        } elseif ($request->input('sort_by') === 'price_desc') {
            $query->orderBy('go_to_price_per_person', 'desc');
        } elseif ($request->input('sort_by') === 'earliest') {
            $query->orderBy('date', 'asc')->orderBy('time', 'asc');
        } else {
            $query->orderBy('date', 'desc')->orderBy('time', 'desc');
        }

        $rides = $query->paginate(15);

        $rideEntries = [];
        foreach ($rides as $ride) {
            $isGoAvailable = false;
            if ($ride->go_completion_status === 'pending') {
                if ($ride->is_exclusive) {
                    $isGoAvailable = ! RidePurchase::where('ride_id', $ride->id)
                        ->where('trip_type', 'go')->exists();
                } else {
                    $isGoAvailable = $ride->available_seats > 0;
                }
            }

            if ($isGoAvailable) {
                $hasBookedGo = false;
                if ($userId) {
                    $hasBookedGo = RidePurchase::where('ride_id', $ride->id)
                        ->where('user_id', $userId)
                        ->where('trip_type', 'go')->exists();
                }
                $rideEntries[] = [
                    'type' => 'Go',
                    'ride' => $ride,
                    'station_location' => $ride->station_location,
                    'destination' => $ride->destination,
                    'date' => $ride->date,
                    'time' => $ride->time,
                    'available_seats' => $ride->is_exclusive ? 1 : $ride->available_seats,
                    'is_exclusive' => $ride->is_exclusive,
                    'price_per_person' => $ride->is_exclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person,
                    'user' => $ride->user,
                    'has_booked' => $hasBookedGo,
                ];
            }

            $isReturnAvailable = false;
            if ($ride->is_two_way && $ride->return_date && $ride->return_time && $ride->return_completion_status === 'pending') {
                if ($ride->return_is_exclusive) {
                    $isReturnAvailable = ! RidePurchase::where('ride_id', $ride->id)
                        ->where('trip_type', 'return')->exists();
                } else {
                    $isReturnAvailable = $ride->return_available_seats > 0;
                }
            }

            if ($isReturnAvailable) {
                $hasBookedReturn = false;
                if ($userId) {
                    $hasBookedReturn = RidePurchase::where('ride_id', $ride->id)
                        ->where('user_id', $userId)
                        ->where('trip_type', 'return')->exists();
                }
                $rideEntries[] = [
                    'type' => 'Back',
                    'ride' => $ride,
                    'station_location' => $ride->destination,
                    'destination' => $ride->station_location,
                    'date' => $ride->return_date,
                    'time' => $ride->return_time,
                    'available_seats' => $ride->return_is_exclusive ? 1 : $ride->return_available_seats,
                    'is_exclusive' => $ride->return_is_exclusive,
                    'price_per_person' => $ride->return_is_exclusive ? $ride->return_exclusive_price : $ride->return_price_per_person,
                    'user' => $ride->user,
                    'has_booked' => $hasBookedReturn,
                ];
            }
        }

        if ($request->filled('price_min')) {
            $min = (float) $request->input('price_min');
            $rideEntries = array_filter($rideEntries, fn ($e) => isset($e['price_per_person']) && $e['price_per_person'] >= $min);
        }
        if ($request->filled('price_max')) {
            $max = (float) $request->input('price_max');
            $rideEntries = array_filter($rideEntries, fn ($e) => isset($e['price_per_person']) && $e['price_per_person'] <= $max);
        }

        if (in_array($request->input('sort_by'), ['price_asc', 'price_desc'])) {
            usort($rideEntries, function ($a, $b) use ($request) {
                $aPrice = $a['price_per_person'] ?? 0;
                $bPrice = $b['price_per_person'] ?? 0;
                if ($aPrice == $bPrice) {
                    return 0;
                }

                return $request->input('sort_by') === 'price_asc' ? $aPrice <=> $bPrice : $bPrice <=> $aPrice;
            });
        }

        return view('find-rides', [
            'rideEntries' => $rideEntries,
            'filters' => $request->all(),
            'rides' => $rides,
        ]);
    }

    public function earnings(Request $request): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $bookings = RidePurchase::with(['ride', 'user'])
            ->whereHas('ride', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $totalEarnings = $bookings->sum('total_price');
        $totalCustomers = $bookings->unique('user_id')->count();

        $completedRides = Ride::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('go_completion_status', 'completed')
                    ->orWhere('return_completion_status', 'completed');
            })
            ->get();

        $totalRidesCompleted = 0;
        foreach ($completedRides as $ride) {
            if ($ride->go_completion_status === 'completed') {
                $totalRidesCompleted++;
            }
            if ($ride->return_completion_status === 'completed') {
                $totalRidesCompleted++;
            }
        }

        /** @phpstan-var view-string $view */
        $view = 'ride-management.earnings';
        return view($view, compact('user', 'bookings', 'totalEarnings', 'totalCustomers', 'totalRidesCompleted'));
    }

    public function showRideCustomers($rideId, $tripType = null): View|RedirectResponse
    {
        $user = $this->getWebUser();
        if (! $user) {
            return $this->webRedirectLogin();
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return redirect()->route('driver.ride.management')->with('error', 'Ride not found or you do not have permission to view it.');
        }

        $query = RidePurchase::with(['user'])->where('ride_id', $rideId);
        if ($tripType) {
            $query->where('trip_type', $tripType);
        }
        $bookings = $query->orderBy('created_at', 'desc')->get();

        $seatInfo = $this->calculateSeatInfo($ride, $tripType, $bookings);

        /** @phpstan-var view-string $view */
        $view = 'ride-management.ride-customers';
        return view($view, compact('user', 'ride', 'bookings', 'tripType', 'seatInfo'));
    }

    private function calculateSeatInfo($ride, $tripType, $bookings)
    {
        if ($tripType === 'return' && $ride->is_two_way) {
            $totalSeats = $ride->return_available_seats + $bookings->where('trip_type', 'return')->sum('number_of_seats');
            $availableSeats = $ride->return_available_seats;
        } else {
            $totalSeats = $ride->available_seats + $bookings->where('trip_type', 'go')->sum('number_of_seats');
            $availableSeats = $ride->available_seats;
        }

        $seatMap = [];
        for ($seat = 1; $seat <= $totalSeats; $seat++) {
            $seatMap[$seat] = [
                'seat_number' => $seat,
                'is_booked' => false,
                'customer_name' => null,
                'booking_reference' => null,
                'contact_phone' => null,
                'passenger_name' => null,
                'booking_id' => null,
            ];
        }

        foreach ($bookings as $booking) {
            if ($booking->selected_seats && is_array($booking->selected_seats)) {
                foreach ($booking->selected_seats as $seatNumber) {
                    if (isset($seatMap[$seatNumber])) {
                        $seatMap[$seatNumber]['is_booked'] = true;
                        $seatMap[$seatNumber]['customer_name'] = $booking->user ? $booking->user->name : 'Unknown';
                        $seatMap[$seatNumber]['booking_reference'] = $booking->booking_reference;
                        $seatMap[$seatNumber]['contact_phone'] = $booking->contact_phone;
                        $seatMap[$seatNumber]['booking_id'] = $booking->id;

                        if ($booking->passenger_details && is_array($booking->passenger_details)) {
                            foreach ($booking->passenger_details as $passenger) {
                                if (isset($passenger['seat_number']) && $passenger['seat_number'] == $seatNumber) {
                                    $seatMap[$seatNumber]['passenger_name'] = $passenger['name'] ?? 'Unknown';
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'total_seats' => $totalSeats,
            'available_seats' => $availableSeats,
            'booked_seats' => $totalSeats - $availableSeats,
            'seat_map' => $seatMap,
        ];
    }

    // API Methods
    public function apiIndex(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to access ride management.', 401);
        }

        $allRides = Ride::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        $goRides = collect();
        $returnRides = collect();

        foreach ($allRides as $ride) {
            $goRides->push($ride);
            if ($ride->is_two_way && $ride->return_date && $ride->return_time) {
                $returnRides->push($ride);
            }
        }

        return $this->jsonSuccess('Ride management data retrieved successfully', [
            'user' => $user,
            'go_rides' => $goRides,
            'return_rides' => $returnRides,
        ]);
    }

    public function apiCreate(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to create a ride.', 401);
        }

        return $this->jsonSuccess('Ride creation form data retrieved successfully', [
            'user' => $user,
            'form_fields' => [
                'station_location' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required',
                'available_seats' => 'required|integer',
                'is_exclusive' => 'required|boolean',
                'is_two_way' => 'required|boolean',
                'return_station_location' => 'nullable|string|max:255',
                'return_destination' => 'nullable|string|max:255',
                'return_date' => 'nullable|date|after_or_equal:date',
                'return_time' => 'nullable',
                'return_available_seats' => 'nullable|integer',
                'return_is_exclusive' => 'nullable|boolean',
                'station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
                'destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
                'return_station_location_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
                'return_destination_map_url' => 'nullable|url|max:255|regex:/^https:\/\/(www\.)?(google\.com\/maps|maps\.google\.com|maps\.app\.goo\.gl)/i',
                'go_to_price_per_person' => 'nullable|numeric|min:0',
                'go_to_exclusive_price' => 'nullable|numeric|min:0',
                'return_price_per_person' => 'nullable|numeric|min:0',
                'return_exclusive_price' => 'nullable|numeric|min:0',
            ],
        ]);
    }

    public function apiStore(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to create a ride.', 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->rideValidationService->rideRules());
        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();

        $errors = $this->rideValidationService->validateApiRidePricing($request, $validated);
        if ($errors) {
            return $this->jsonError('Validation failed', 422, $errors);
        }

        $errors = $this->rideValidationService->validateApiReturnPricing($request, $validated);
        if ($errors) {
            return $this->jsonError('Validation failed', 422, $errors);
        }

        $validated['return_destination'] = $validated['station_location'];
        $ride = new Ride($validated);
        $ride->user_id = $user->id;
        $ride->save();

        return $this->jsonSuccess('Ride created successfully!', ['ride' => $ride]);
    }

    public function apiMyRides(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to view your rides.', 401);
        }

        $rides = Ride::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(15);

        return $this->jsonPaginated('My rides retrieved successfully', $rides);
    }

    public function apiEdit(Request $request, $rideId): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to edit a ride.', 401);
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        return $this->jsonSuccess('Ride edit form data retrieved successfully', [
            'user' => $user,
            'ride' => $ride,
        ]);
    }

    public function apiUpdate(Request $request, $rideId): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to update a ride.', 401);
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->rideValidationService->rideRules());
        if ($validator->fails()) {
            return $this->jsonError('Validation failed', 422, $validator->errors());
        }

        $validated = $validator->validated();

        $errors = $this->rideValidationService->validateApiRidePricing($request, $validated);
        if ($errors) {
            return $this->jsonError('Validation failed', 422, $errors);
        }

        $errors = $this->rideValidationService->validateApiReturnPricing($request, $validated);
        if ($errors) {
            return $this->jsonError('Validation failed', 422, $errors);
        }

        $validated['return_destination'] = $validated['station_location'];
        $ride->update($validated);

        return $this->jsonSuccess('Ride updated successfully!', ['ride' => $ride]);
    }

    public function apiFindRides(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to find rides.', 401);
        }

        $rides = Ride::with('user')
            ->where('status', 'active')
            ->where('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->paginate(15);

        return $this->jsonPaginated('Available rides found successfully', $rides);
    }

    public function apiEarnings(Request $request): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to view earnings.', 401);
        }

        $bookings = RidePurchase::with(['ride', 'user'])
            ->whereHas('ride', fn ($q) => $q->where('user_id', $user->id))
            ->where('payment_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalEarnings = RidePurchase::whereHas('ride', fn ($q) => $q->where('user_id', $user->id))
            ->where('payment_status', 'completed')
            ->sum('total_price');

        return $this->jsonSuccess('Earnings data retrieved successfully', [
            'user' => $user,
            'earnings' => $bookings->items(),
            'total_earnings' => $totalEarnings,
            'meta' => [
                'total' => $bookings->total(),
                'page' => $bookings->currentPage(),
                'per_page' => $bookings->perPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    public function apiShowRideCustomers(Request $request, $rideId, $tripType = null): JsonResponse
    {
        $user = $this->getApiUser($request);
        if (! $user) {
            return $this->jsonError('Please login to view ride customers.', 401);
        }

        $ride = Ride::where('id', $rideId)->where('user_id', $user->id)->first();
        if (! $ride) {
            return $this->jsonError('Ride not found.', 404);
        }

        $query = RidePurchase::with('user')
            ->where('ride_id', $rideId)
            ->where('seats_confirmed', true);

        if ($tripType) {
            $query->where('trip_type', $tripType);
        }

        $bookings = $query->orderBy('created_at', 'desc')->get();

        return $this->jsonSuccess('Ride customers data retrieved successfully', [
            'user' => $user,
            'ride' => $ride,
            'bookings' => $bookings,
            'trip_type' => $tripType,
        ]);
    }
}
