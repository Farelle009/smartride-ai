<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideOrder extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'service_type',
        'pickup',
        'destination',
        'pickup_lat',
        'pickup_lng',
        'destination_lat',
        'destination_lng',
        'distance',
        'time_condition',
        'payment_method',
        'base_price',
        'price_per_km',
        'normal_price',
        'ai_price',
        'surge_percentage',
        'discount',
        'price_after_promo',
        'nego_price',
        'final_price',
        'promo_code',
        'promo_status',
        'nego_status',
        'fallback_status',
        'surge_status',
        'driver_name',
        'driver_vehicle',
        'driver_plate',
        'driver_distance_to_pickup',
        'driver_matching_score',
        'driver_reliability',
        'quality_average',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}