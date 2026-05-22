<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRideController extends Controller
{
    public function index(): View
    {
        $rides = Ride::with('driver')->paginate(10);

        /** @phpstan-var view-string $view */
        $view = 'admin.rides.index';
        return view($view, compact('rides'));
    }

    public function create(): View
    {
        $drivers = User::where('role', 'driver')->get();

        /** @phpstan-var view-string $view */
        $view = 'admin.rides.create';
        return view($view, compact('drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date|after:today',
            'time' => 'required',
            'available_seats' => 'required|integer|min:1|max:20',
            'is_exclusive' => 'boolean',
            'is_two_way' => 'boolean',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
        ]);

        Ride::create($validated);

        return redirect()->route('admin.rides.index')->with('success', 'Ride created successfully');
    }

    public function edit($id): View
    {
        $ride = Ride::with('driver')->findOrFail($id);
        $drivers = User::where('role', 'driver')->get();

        /** @phpstan-var view-string $view */
        $view = 'admin.rides.edit';
        return view($view, compact('ride', 'drivers'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $ride = Ride::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'available_seats' => 'required|integer|min:1|max:20',
            'is_exclusive' => 'boolean',
            'is_two_way' => 'boolean',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
        ]);

        $ride->update($validated);

        return redirect()->route('admin.rides.index')->with('success', 'Ride updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        $ride = Ride::findOrFail($id);
        $ride->delete();

        return redirect()->route('admin.rides.index')->with('success', 'Ride deleted successfully');
    }

    public function passengers($id): View
    {
        $ride = Ride::findOrFail($id);
        $bookings = RidePurchase::with('user')->where('ride_id', $id)->get();

        /** @phpstan-var view-string $view */
        $view = 'admin.rides.passengers';
        return view($view, compact('ride', 'bookings'));
    }

    // API: List rides
    public function apiIndex(): JsonResponse
    {
        $rides = Ride::with('driver')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $rides,
        ]);
    }

    // API: Create ride
    public function apiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date|after:today',
            'time' => 'required',
            'available_seats' => 'required|integer|min:1|max:20',
            'is_exclusive' => 'boolean',
            'is_two_way' => 'boolean',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
        ]);
        $ride = Ride::create($validated);

        return response()->json([
            'success' => true,
            'data' => $ride,
        ]);
    }

    // API: Show ride
    public function apiShow($id): JsonResponse
    {
        $ride = Ride::with('driver')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ride,
        ]);
    }

    // API: Update ride
    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $ride = Ride::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'station_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'available_seats' => 'required|integer|min:1|max:20',
            'is_exclusive' => 'boolean',
            'is_two_way' => 'boolean',
            'go_to_price_per_person' => 'nullable|numeric|min:0',
            'go_to_exclusive_price' => 'nullable|numeric|min:0',
        ]);
        $ride->update($validated);

        return response()->json([
            'success' => true,
            'data' => $ride,
        ]);
    }

    // API: Delete ride
    public function apiDestroy($id): JsonResponse
    {
        $ride = Ride::findOrFail($id);
        $ride->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ride deleted successfully.',
        ]);
    }

    // API: List passengers for a ride
    public function apiPassengers($id): JsonResponse
    {
        $ride = Ride::findOrFail($id);
        $bookings = RidePurchase::with('user')->where('ride_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ride' => $ride,
                'bookings' => $bookings,
            ],
        ]);
    }
}
