<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\RideReview;
use App\Models\User;

class DriverStatsService
{
    public function getDriverStats(User $driver): array
    {
        return [
            'total_rides' => $driver->rides()->count(),
            'completed_rides' => $driver->rides()
                ->where(function ($q) {
                    $q->where('go_completion_status', 'completed')
                      ->orWhere('return_completion_status', 'completed');
                })
                ->count(),
            'total_earnings' => $driver->ridePurchases()
                ->where('payment_status', 'completed')
                ->sum('total_price'),
            'recent_rides' => $driver->rides()
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->take(5)
                ->get(),
            'average_rating' => RideReview::whereHas('ride', function ($q) use ($driver) {
                $q->where('user_id', $driver->id);
            })->avg('overall_rating') ?? 0,
            'monthly_earnings' => $driver->ridePurchases()
                ->where('payment_status', 'completed')
                ->where('created_at', '>=', now()->subMonths(6))
                ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_price) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get(),
        ];
    }

    public function getPublicProfileStats(User $driver): array
    {
        $reviews = RideReview::with(['ride', 'user', 'ridePurchase'])
            ->whereHas('ride', function ($q) use ($driver) {
                $q->where('user_id', $driver->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $reviews->count();

        $averageOverallRating = $totalReviews > 0
            ? RideReview::whereHas('ride', fn($q) => $q->where('user_id', $driver->id))->avg('overall_rating')
            : 0;
        $averageDriverRating = $totalReviews > 0
            ? RideReview::whereHas('ride', fn($q) => $q->where('user_id', $driver->id))->avg('driver_rating')
            : 0;
        $averageVehicleRating = $totalReviews > 0
            ? RideReview::whereHas('ride', fn($q) => $q->where('user_id', $driver->id))->avg('vehicle_rating')
            : 0;

        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = RideReview::whereHas('ride', fn($q) => $q->where('user_id', $driver->id))
                ->where('overall_rating', $i)
                ->count();
        }

        $previousRides = Ride::where('user_id', $driver->id)
            ->where(function ($q) {
                $q->where('go_completion_status', 'completed')
                  ->orWhere('return_completion_status', 'completed');
            })
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $filteredAvailableRides = Ride::where('user_id', $driver->id)
            ->where('go_completion_status', 'pending')
            ->get()
            ->filter(function ($ride) {
                if ($ride->is_exclusive) {
                    return !RidePurchase::where('ride_id', $ride->id)->where('trip_type', 'go')->exists();
                }
                return $ride->available_seats > 0;
            })
            ->take(10);

        $filteredReturnRides = Ride::where('user_id', $driver->id)
            ->where('is_two_way', true)
            ->where('return_completion_status', 'pending')
            ->get()
            ->filter(function ($ride) {
                if ($ride->return_is_exclusive) {
                    return !RidePurchase::where('ride_id', $ride->id)->where('trip_type', 'return')->exists();
                }
                return $ride->return_available_seats > 0;
            })
            ->take(5);

        return [
            'reviews' => $reviews,
            'total_reviews' => $totalReviews,
            'average_overall_rating' => $averageOverallRating,
            'average_driver_rating' => $averageDriverRating,
            'average_vehicle_rating' => $averageVehicleRating,
            'rating_distribution' => $ratingDistribution,
            'previous_rides' => $previousRides,
            'filtered_available_rides' => $filteredAvailableRides,
            'filtered_return_rides' => $filteredReturnRides,
        ];
    }

    public function getReviewsStats(User $driver): array
    {
        $reviews = RideReview::with(['ridePurchase.user', 'ridePurchase.ride'])
            ->whereHas('ridePurchase.ride', function ($q) use ($driver) {
                $q->where('user_id', $driver->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $reviews->count();
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $reviews->where('overall_rating', $i)->count();
        }

        return [
            'reviews' => $reviews,
            'total_reviews' => $totalReviews,
            'average_overall_rating' => $totalReviews > 0 ? $reviews->avg('overall_rating') : 0,
            'average_driver_rating' => $totalReviews > 0 ? $reviews->avg('driver_rating') : 0,
            'average_vehicle_rating' => $totalReviews > 0 ? $reviews->avg('vehicle_rating') : 0,
            'rating_distribution' => $ratingDistribution,
        ];
    }
}
