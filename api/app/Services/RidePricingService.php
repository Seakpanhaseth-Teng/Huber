<?php

namespace App\Services;

use App\Models\Ride;

class RidePricingService
{
    public function getPricing(Ride $ride, string $tripType): array
    {
        if ($tripType === 'return' && $ride->is_two_way) {
            $isExclusive = $ride->return_is_exclusive;
            return [
                'price_per_seat' => $isExclusive ? $ride->return_exclusive_price : $ride->return_price_per_person,
                'is_exclusive' => $isExclusive,
                'available_seats' => $ride->return_available_seats,
                'date' => $ride->return_date,
                'time' => $ride->return_time,
            ];
        }

        $isExclusive = $ride->is_exclusive;
        return [
            'price_per_seat' => $isExclusive ? $ride->go_to_exclusive_price : $ride->go_to_price_per_person,
            'is_exclusive' => $isExclusive,
            'available_seats' => $ride->available_seats,
            'date' => $ride->date,
            'time' => $ride->time,
        ];
    }

    public function getPricingSimple(Ride $ride, string $tripType): array
    {
        if ($tripType === 'return' && $ride->is_two_way) {
            return [
                'available_seats' => $ride->return_available_seats,
                'date' => $ride->return_date,
                'time' => $ride->return_time,
                'price_per_seat' => $ride->return_price_per_person,
            ];
        }

        return [
            'available_seats' => $ride->available_seats,
            'date' => $ride->date,
            'time' => $ride->time,
            'price_per_seat' => $ride->go_to_price_per_person,
        ];
    }

    public function calculateTotalPrice(Ride $ride, string $tripType, int $numberOfSeats): float
    {
        $pricing = $this->getPricing($ride, $tripType);
        return $pricing['is_exclusive']
            ? (float) $pricing['price_per_seat']
            : (float) $pricing['price_per_seat'] * $numberOfSeats;
    }
}
