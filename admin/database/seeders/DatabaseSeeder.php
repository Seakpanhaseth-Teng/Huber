<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $adminPassword = env('ADMIN_PASSWORD');
        if (! $adminPassword) {
            throw new \RuntimeException('ADMIN_PASSWORD environment variable must be set to seed the admin account.');
        }

        Admin::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@huber.com')],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
            ]
        );
    }
}
