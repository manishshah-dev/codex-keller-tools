<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Search criteria
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Create regular user
        User::updateOrCreate(
            ['email' => 'manager@example.com'], // Search criteria
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );
    }
}