<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ride_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('order_number');
            $table->string('service_type');
            $table->string('pickup');
            $table->string('destination');

            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->decimal('destination_lat', 10, 7);
            $table->decimal('destination_lng', 10, 7);

            $table->decimal('distance', 8, 2);
            $table->string('time_condition');
            $table->string('payment_method');

            $table->integer('base_price');
            $table->integer('price_per_km');
            $table->integer('normal_price');
            $table->integer('ai_price');
            $table->integer('surge_percentage');
            $table->integer('discount')->default(0);
            $table->integer('price_after_promo');
            $table->integer('nego_price')->nullable();
            $table->integer('final_price');

            $table->string('promo_code')->nullable();
            $table->text('promo_status')->nullable();
            $table->text('nego_status')->nullable();
            $table->text('fallback_status')->nullable();
            $table->text('surge_status')->nullable();

            $table->string('driver_name');
            $table->string('driver_vehicle');
            $table->string('driver_plate');
            $table->decimal('driver_distance_to_pickup', 8, 2);
            $table->decimal('driver_matching_score', 8, 2);
            $table->integer('driver_reliability');

            $table->decimal('quality_average', 8, 2);

            $table->string('status')->default('Selesai');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_orders');
    }
};