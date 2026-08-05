<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WorkMode;
use App\Models\Application;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $wroclawUniversity = University::factory()->approved()->create([
            "name" => "Politechnika Wrocławska",
            "domain" => "pwr.edu.pl",
        ]);

        User::factory()->create([
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "email" => "jan.kowalski@pwr.edu.pl",
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "organization_id" => null,
        ]);

        User::factory()->create([
            "first_name" => "Super",
            "last_name" => "Admin",
            "email" => "admin@example.com",
            "role" => UserRole::SuperAdmin,
            "email_verified_at" => now(),
            "status" => UserStatus::Active,
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

        User::factory()->create([
            "email" => "company-pending@example.com",
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $pendingCompany->id,
            "first_name" => null,
            "last_name" => null,
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
            "domain" => "example.com",
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

        User::factory()->create([
            "email" => "university-pending@example.com",
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $pendingUniversity->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        Company::factory()->count(30)->create()->each(function ($company): void {
            User::factory()->create([
                "role" => UserRole::CompanyAdmin,
                "status" => UserStatus::Pending,
                "email_verified_at" => null,
                "organization_id" => $company->id,
                "first_name" => null,
                "last_name" => null,
            ]);
        });

        University::factory()->count(60)->create()->each(function ($university): void {
            User::factory()->create([
                "role" => UserRole::UniversityAdmin,
                "status" => UserStatus::Pending,
                "email_verified_at" => null,
                "organization_id" => $university->id,
                "first_name" => null,
                "last_name" => null,
            ]);
        });

        $offers = Offer::factory()->count(4)->create([
            "company_id" => $approvedCompany->id,
            "spots" => 5,
        ]);

        $statuses = [
            ApplicationStatus::Pending,
            ApplicationStatus::Reviewed,
            ApplicationStatus::Accepted,
            ApplicationStatus::Rejected,
        ];

        foreach ($statuses as $index => $status) {
            $student = User::factory()->create([
                "role" => UserRole::Student,
                "status" => UserStatus::Active,
                "cv_path" => "cvs/demo_cv_" . $index . ".pdf",
            ]);

            Application::factory()->create([
                "offer_id" => $offers[$index]->id,
                "student_id" => $student->id,
                "status" => $status,
            ]);
        }

        $faculty = Faculty::factory()->for($approvedUniversity)->create([
            "name" => "Wydział Informatyki",
        ]);

        $studyFields = StudyField::factory()->for($faculty)->count(5)->create();

        $cities = ["Warszawa", "Kraków", "Wrocław", "Poznań", "Gdańsk", "Łódź"];
        $workModes = WorkMode::cases();

        foreach ($cities as $index => $city) {
            $offer = Offer::factory()->create([
                "company_id" => $approvedCompany->id,
                "city" => $city,
                "work_mode" => $workModes[$index % count($workModes)],
            ]);

            $offer->studyFields()->attach(
                $studyFields->random(random_int(1, 2))->pluck("id"),
            );
        }
    }
}
