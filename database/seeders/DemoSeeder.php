<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
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

        $approvedCompany = Company::factory()->approved()->create([
            "name" => "Approved Company Sp. z o.o.",
            "email" => "approved@example.com",
        ]);

        User::factory()->create([
            "email" => "company-approved@example.com",
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $approvedCompany->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        $pendingCompany = Company::factory()->pending()->create([
            "name" => "Pending Company Sp. z o.o.",
            "email" => "pending@example.com",
        ]);

        User::factory()->pendingCompanyAdmin()->create([
            "email" => "company-pending@example.com",
            "organization_id" => $pendingCompany->id,
        ]);
    }
}
