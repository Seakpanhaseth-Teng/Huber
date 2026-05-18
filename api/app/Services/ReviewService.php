<?php

namespace App\Services;

use App\Models\RidePurchase;
use App\Models\RideReview;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function getBookingWithOwnershipCheck(int $bookingId, int $userId): ?RidePurchase
    {
        return RidePurchase::with(['ride'])
            ->where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getRideStatus(RidePurchase $booking, string $tripType): string
    {
        /** @var \App\Models\Ride $ride */
        $ride = $booking->ride;

        return $tripType === 'return'
            ? $ride->return_completion_status
            : $ride->go_completion_status;
    }

    public function getExistingReview(int $bookingId, string $tripType): ?RideReview
    {
        return RideReview::where('ride_purchase_id', $bookingId)
            ->where('trip_type', $tripType)
            ->first();
    }

    public function createReview(
        RidePurchase $booking,
        int $userId,
        string $tripType,
        array $ratings,
        ?string $reviewText
    ): RideReview {
        try {
            DB::beginTransaction();

            $review = RideReview::create([
                'ride_id' => $booking->ride_id,
                'user_id' => $userId,
                'ride_purchase_id' => $booking->id,
                'overall_rating' => $ratings['overall_rating'],
                'driver_rating' => $ratings['driver_rating'],
                'vehicle_rating' => $ratings['vehicle_rating'],
                'punctuality_rating' => $ratings['punctuality_rating'],
                'safety_rating' => $ratings['safety_rating'],
                'comfort_rating' => $ratings['comfort_rating'],
                'review_text' => $reviewText,
                'trip_type' => $tripType,
                'status' => 'approved',
            ]);

            DB::commit();

            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
