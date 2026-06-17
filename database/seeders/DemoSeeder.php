<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            "first_name" => "Super",
            "last_name" => "Admin",
            "email" => "admin@example.com",
            "role" => UserRole::SuperAdmin,
            "email_verified_at" => now(),
        ]);

        User::factory()->create([
            "first_name" => "Test",
            "last_name" => "User",
            "email" => "user@example.com",
            "role" => UserRole::Student,
        ]);
    }
}
