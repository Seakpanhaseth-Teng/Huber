<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\RideReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Services\DriverStatsService;

class AdminDriverController extends Controller
{
    protected $driverStatsService;

    public function __construct()
    {
        $this->driverStatsService = app(DriverStatsService::class);
    }

    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->with(['rides', 'driverDocuments'])
            ->paginate(12);
        return view('admin.drivers.index', compact('drivers'));
    }

    public function show($id)
    {
        $driver = User::where('role', 'driver')
            ->with(['rides', 'driverDocuments', 'ridePurchases'])
            ->findOrFail($id);

        $stats = Cache::remember("driver_stats_{$id}", 300, function () use ($driver) {
            return $this->driverStatsService->getDriverStats($driver);
        });

        return view('admin.drivers.show', compact(
            'driver',
            'stats'
        ) + [
            'totalRides' => $stats['total_rides'],
            'completedRides' => $stats['completed_rides'],
            'totalEarnings' => $stats['total_earnings'],
            'recentRides' => $stats['recent_rides'],
            'averageRating' => $stats['average_rating'],
            'monthlyEarnings' => $stats['monthly_earnings'],
        ]);
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'driver',
            'is_verified' => false,
        ]);
        return redirect()->route('admin.drivers.index')->with('success', 'Driver created successfully');
    }

    public function edit($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        return view('admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $driver->id,
            'password' => 'nullable|min:8',
        ]);
        $driver->name = $request->name;
        $driver->email = $request->email;
        if ($request->password) {
            $driver->password = Hash::make($request->password);
        }
        $driver->save();
        return redirect()->route('admin.drivers.index')->with('success', 'Driver updated successfully');
    }

    public function destroy($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->delete();
        return redirect()->route('admin.drivers.index')->with('success', 'Driver deleted successfully');
    }

    // API Methods
    public function apiIndex()
    {
        $drivers = User::where('role', 'driver')->with(['rides', 'driverDocuments'])->paginate(12);
        return response()->json([
            'success' => true,
            'data' => $drivers,
        ]);
    }

    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);
        $driver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'driver',
            'is_verified' => false,
        ]);
        return response()->json(['success' => true, 'data' => $driver]);
    }

    public function apiShow($id)
    {
        $driver = User::where('role', 'driver')
            ->with(['rides', 'driverDocuments', 'ridePurchases'])
            ->findOrFail($id);

        $stats = $this->driverStatsService->getDriverStats($driver);

        return response()->json([
            'success' => true,
            'data' => [
                'driver' => $driver,
                'total_rides' => $stats['total_rides'],
                'completed_rides' => $stats['completed_rides'],
                'total_earnings' => $stats['total_earnings'],
                'recent_rides' => $stats['recent_rides'],
                'average_rating' => $stats['average_rating'],
                'monthly_earnings' => $stats['monthly_earnings'],
            ],
        ]);
    }

    public function apiUpdate(Request $request, $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $driver->id,
            'password' => 'nullable|min:8',
        ]);
        $driver->name = $validated['name'];
        $driver->email = $validated['email'];
        if (!empty($validated['password'])) {
            $driver->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }
        $driver->save();
        return response()->json(['success' => true, 'data' => $driver]);
    }

    public function apiDestroy($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->delete();
        return response()->json(['success' => true, 'message' => 'Driver deleted successfully.']);
    }
}
