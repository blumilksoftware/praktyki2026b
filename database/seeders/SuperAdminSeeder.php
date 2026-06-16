<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            "first_name" => "Super",
            "last_name" => "Admin",
            "email" => "admin@applikuj.pl",
            "role" => UserRole::SuperAdmin,
            "email_verified_at" => now(),
        ]);
    }
}
