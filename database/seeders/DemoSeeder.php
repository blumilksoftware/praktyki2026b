<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\University;
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

        $approvedUniversity = University::factory()->approved()->create([
            "name" => "Politechnika Przykładowa",
            "email" => "approved@university.example.com",
            "domain" => "university.example.com",
        ]);

        User::factory()->create([
            "email" => "university-approved@example.com",
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $approvedUniversity->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        $pendingUniversity = University::factory()->pending()->create([
            "name" => "Akademia Oczekująca",
            "email" => "pending@university.example.com",
            "domain" => "pending-university.example.com",
        ]);

        User::factory()->pendingUniversityAdmin()->create([
            "email" => "university-pending@example.com",
            "organization_id" => $pendingUniversity->id,
        ]);
    }
}
