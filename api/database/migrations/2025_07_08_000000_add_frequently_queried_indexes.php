<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // rides.user_id — queried in every driver-related operation
        Schema::table('rides', function (Blueprint $table) {
            $table->index('user_id');
        });

        // ride_purchases: composite index for seat availability checks
        Schema::table('ride_purchases', function (Blueprint $table) {
            $table->index('ride_id');
            $table->index('user_id');
            $table->index(['ride_id', 'trip_type', 'seats_confirmed']);
        });

        // ride_reviews
        Schema::table('ride_reviews', function (Blueprint $table) {
            $table->index('ride_id');
            $table->index('user_id');
            $table->index('ride_purchase_id');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('ride_purchases', function (Blueprint $table) {
            $table->dropIndex(['ride_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['ride_id', 'trip_type', 'seats_confirmed']);
        });

        Schema::table('ride_reviews', function (Blueprint $table) {
            $table->dropIndex(['ride_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['ride_purchase_id']);
        });
    }
};