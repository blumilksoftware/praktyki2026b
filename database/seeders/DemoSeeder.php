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
    private array $citiesWithCoordinates = [
        "Warszawa" => ["lat" => 52.2297, "lng" => 21.0122],
        "Kraków" => ["lat" => 50.0647, "lng" => 19.9450],
        "Wrocław" => ["lat" => 51.1100, "lng" => 17.0385],
        "Poznań" => ["lat" => 52.4064, "lng" => 16.9299],
        "Gdańsk" => ["lat" => 54.3520, "lng" => 18.6466],
        "Łódź" => ["lat" => 51.7592, "lng" => 19.4560],
    ];

    public function run(): void
    {
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

        User::factory()->count(25)->companyMember()->create([
            "organization_id" => $approvedCompany->id,
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

        User::factory()->create([
            "email" => "university-pending@example.com",
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $pendingUniversity->id,
            "first_name" => null,
            "last_name" => null,
        ]);

        $awaitingApprovalUniversity = University::factory()->pending()->create([
            "name" => "Awaiting Approval University",
            "email" => "awaiting-approval-university@example.com",
            "domain" => "awaiting-approval-university.example.com",
        ]);

        User::factory()->create([
            "email" => "university-awaiting-approval@example.com",
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $awaitingApprovalUniversity->id,
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

        $offers = Offer::factory()->count(15)->create([
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

        $studyFields = StudyField::factory()->for($faculty)->count(20)->create();

        $workModes = WorkMode::cases();
        $cityNames = array_keys($this->citiesWithCoordinates);

        foreach ($cityNames as $index => $cityName) {
            for ($i = 0; $i < 2; $i++) {
                $coords = $this->citiesWithCoordinates[$cityName];

                $latJitter = mt_rand(-15, 15) / 1000;
                $lngJitter = mt_rand(-15, 15) / 1000;

                $offer = Offer::factory()->create([
                    "company_id" => $approvedCompany->id,
                    "city" => $cityName,
                    "latitude" => $coords["lat"] + $latJitter,
                    "longitude" => $coords["lng"] + $lngJitter,
                    "work_mode" => $workModes[$index % count($workModes)],
                ]);

                $offer->studyFields()->attach(
                    $studyFields->random(random_int(1, 2))->pluck("id"),
                );
            }
        }

        $domainLinkedStudents = User::factory()->count(20)->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => fn() => fake()->unique()->userName() . "@" . $approvedUniversity->domain,
            "study_field" => fn() => $studyFields->random()->id,
        ]);

        $explicitlyLinkedStudents = User::factory()->count(3)->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "organization_id" => $approvedUniversity->id,
            "study_field" => fn() => $studyFields->random()->id,
        ]);

        User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "unrelated-student@example.com",
        ]);

        $allLinkedStudents = $domainLinkedStudents->merge($explicitlyLinkedStudents);

        foreach ($allLinkedStudents as $index => $student) {
            $createdAt = match ($index % 3) {
                0 => now()->subDays(15),
                1 => now()->subDays(30),
                default => now(),
            };

            Application::factory()->create([
                "offer_id" => $offers[$index % $offers->count()]->id,
                "student_id" => $student->id,
                "status" => ApplicationStatus::Pending,
                "created_at" => $createdAt,
                "updated_at" => $createdAt,
                "reminder_14_sent_at" => null,
                "reminder_28_sent_at" => null,
            ]);
        }
    }
}
