<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Hana Admin', 'email' => 'admin@example.com', 'role' => UserRole::Admin],
            ['name' => 'Satria Sales', 'email' => 'sales@example.com', 'role' => UserRole::Sales],
            ['name' => 'Maya Manager', 'email' => 'manager@example.com', 'role' => UserRole::Manager],
            ['name' => 'Raka Finance', 'email' => 'finance@example.com', 'role' => UserRole::Finance],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}
