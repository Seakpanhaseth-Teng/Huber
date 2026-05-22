<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
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

        /** @phpstan-var view-string $view */
        $view = 'admin.dashboard';
        return view($view, $stats);
    }

    public function apiIndex(): JsonResponse
    {
        $stats = Cache::remember('dashboard_api_stats', 300, function () {
            /** @var object{total_users: int, total_drivers: int, total_verified_drivers: int} $userStats */
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
