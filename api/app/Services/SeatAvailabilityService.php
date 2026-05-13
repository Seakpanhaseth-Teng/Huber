<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RidePurchase;
use Illuminate\Support\Facades\DB;

class SeatAvailabilityService
{
    public function getBookedSeatsSql(int $rideId, string $tripType): array
    {
        return DB::table('ride_purchases')
            ->where('ride_id', $rideId)
            ->where('trip_type', $tripType)
            ->where('seats_confirmed', true)
            ->selectRaw('json_extract(selected_seats, \'$[*]\') as seats')
            ->get()
            ->flatMap(function ($row) {
                return json_decode($row->seats, true) ?? [];
            })
            ->toArray();
    }

    public function getBookedSeatsEloquent(int $rideId, string $tripType): array
    {
        return RidePurchase::where('ride_id', $rideId)
            ->where('trip_type', $tripType)
            ->where('seats_confirmed', true)
            ->pluck('selected_seats')
            ->flatten()
            ->filter()
            ->toArray();
    }

    public function checkAvailability(Ride $ride, string $tripType, int $seatsRequired = 1): bool
    {
        $pricing = app(RidePricingService::class)->getPricing($ride, $tripType);
        return $pricing['available_seats'] >= $seatsRequired;
    }

    public function getConflictingSeats(int $rideId, string $tripType, array $selectedSeats): array
    {
        $bookedSeats = $this->getBookedSeatsEloquent($rideId, $tripType);
        return array_intersect($selectedSeats, $bookedSeats);
    }
}
