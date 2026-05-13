<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'card_number_hash',
                'card_expiry_hash',
                'card_cvv_hash',
                'card_holder_name',
            ]);

            $table->string('payment_reference', 100)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('ride_purchases', function (Blueprint $table) {
            $table->dropColumn('payment_reference');

            $table->string('card_number_hash')->nullable()->after('payment_method');
            $table->string('card_expiry_hash')->nullable()->after('card_number_hash');
            $table->string('card_cvv_hash')->nullable()->after('card_expiry_hash');
            $table->string('card_holder_name')->nullable()->after('card_cvv_hash');
        });
    }
};
