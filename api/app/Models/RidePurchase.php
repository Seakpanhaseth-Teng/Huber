<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $ride_id
 * @property int $user_id
 * @property int $number_of_seats
 * @property string $total_price
 * @property string|null $total_amount
 * @property string $payment_status
 * @property string $payment_method
 * @property string|null $payment_reference
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $special_requests
 * @property string $trip_type
 * @property array|null $passenger_details
 * @property array|null $passenger_names
 * @property array|null $selected_seats
 * @property bool $seats_confirmed
 * @property string|null $contact_phone
 * @property string $booking_reference
 * @property \Illuminate\Support\Carbon|null $booking_date
 * @property \Illuminate\Support\Carbon|null $booking_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ride|null $ride
 * @property-read \App\Models\User|null $user
 */
class RidePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'user_id',
        'number_of_seats',
        'total_price',
        'total_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_date',
        'special_requests',
        'trip_type',
        'passenger_details',
        'passenger_names',
        'selected_seats',
        'seats_confirmed',
        'contact_phone',
        'booking_reference',
        'booking_date',
        'booking_time',
    ];

    protected $casts = [
        'passenger_details' => 'array',
        'passenger_names' => 'array',
        'selected_seats' => 'array',
        'seats_confirmed' => 'boolean',
        'total_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'payment_date' => 'datetime',
    ];

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(RideReview::class);
    }
}
