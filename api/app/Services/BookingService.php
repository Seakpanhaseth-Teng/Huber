<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RidePurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function generateBookingReference(): string
    {
        return 'BK' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public function createBooking(
        Ride $ride,
        User $user,
        string $tripType,
        int $numberOfSeats,
        float $totalPrice,
        array $passengerDetails,
        string $contactPhone,
        ?string $specialRequests,
        string $paymentMethod,
        ?string $date = null,
        ?string $time = null
    ): RidePurchase {
        $bookingReference = $this->generateBookingReference();
        $pricing = app(RidePricingService::class)->getPricing($ride, $tripType);
        $paymentReference = 'PAY-' . strtoupper(bin2hex(random_bytes(8)));

        return RidePurchase::create([
            'ride_id' => $ride->id,
            'user_id' => $user->id,
            'number_of_seats' => $numberOfSeats,
            'total_price' => $totalPrice,
            'payment_status' => 'completed',
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'special_requests' => $specialRequests ?? '',
            'trip_type' => $tripType,
            'passenger_details' => $passengerDetails,
            'contact_phone' => $contactPhone,
            'booking_reference' => $bookingReference,
            'booking_date' => $date ?? $pricing['date'],
            'booking_time' => $time ?? $pricing['time'],
        ]);
    }

    public function processBookingTransaction(
        Ride $ride,
        User $user,
        string $tripType,
        int $numberOfSeats,
        float $totalPrice,
        array $passengerDetails,
        string $contactPhone,
        ?string $specialRequests,
        string $paymentMethod
    ): RidePurchase {
        try {
            DB::beginTransaction();

            $booking = $this->createBooking(
                $ride, $user, $tripType, $numberOfSeats, $totalPrice,
                $passengerDetails, $contactPhone, $specialRequests,
                $paymentMethod
            );

            $this->decrementSeats($ride->id, $tripType, $numberOfSeats);

            DB::commit();

            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function decrementSeats(int $rideId, string $tripType, int $numberOfSeats): void
    {
        if ($tripType === 'return') {
            DB::table('rides')
                ->where('id', $rideId)
                ->where('return_available_seats', '>=', $numberOfSeats)
                ->decrement('return_available_seats', $numberOfSeats);
        } else {
            DB::table('rides')
                ->where('id', $rideId)
                ->where('available_seats', '>=', $numberOfSeats)
                ->decrement('available_seats', $numberOfSeats);
        }
    }
}
