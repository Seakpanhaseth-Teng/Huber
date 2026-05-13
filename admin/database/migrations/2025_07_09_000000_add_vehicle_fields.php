<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->string('make', 100)->nullable()->after('user_id');
            $table->string('model', 100)->nullable()->after('make');
            $table->year('year')->nullable()->after('model');
            $table->string('color', 50)->nullable()->after('year');
            $table->string('license_plate', 20)->nullable()->after('color');
            $table->integer('seats')->default(4)->after('license_plate');
            $table->string('photo', 255)->nullable()->after('seats');
        });

        Schema::table('rides', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'make', 'model', 'year', 'color',
                'license_plate', 'seats', 'photo',
            ]);
        });
    }
};
