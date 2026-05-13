<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ride;
use App\Models\DriverDocument;
use App\Models\RidePurchase;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'totalUsers' => User::count(),
                'totalDrivers' => User::where('role', 'driver')->count(),
                'totalVerifiedDrivers' => User::where('role', 'driver')->where('is_verified', true)->count(),
                'totalRides' => Ride::count(),
                'totalEarnings' => RidePurchase::where('payment_status', 'completed')->sum('total_price'),
            ];
        });

        return view('admin.dashboard', $stats);
    }

    public function apiIndex()
    {
        $stats = Cache::remember('dashboard_api_stats', 300, function () {
            $userStats = User::selectRaw('count(*) as total_users,
                count(case when role = "driver" then 1 end) as total_drivers,
                count(case when role = "driver" and is_verified then 1 end) as total_verified_drivers')
                ->first();

            return [
                'total_users' => $userStats->total_users,
                'total_drivers' => $userStats->total_drivers,
                'total_verified_drivers' => $userStats->total_verified_drivers,
                'total_rides' => Ride::count(),
                'total_earnings' => RidePurchase::where('payment_status', 'completed')->sum('total_price'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
