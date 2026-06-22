<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Mail\Verification\CompanyVerificationAcceptMail;
use App\Mail\Verification\CompanyVerificationRejectMail;
use App\Mail\Verification\UniversityVerificationAcceptMail;
use App\Mail\Verification\UniversityVerificationRejectMail;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyAndUniversityVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminDashboardShowCompaniesAndUniversitiesNeedingVerification(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $company = Company::factory()->pending()->create();
        $university = University::factory()->pending()->create();

        $this->actingAs($user)
            ->get("/admin/dashboard")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Dashboard")
                    ->has("companiesNeedingVerification", 1)
                    ->has("universitiesNeedingVerification", 1)
                    ->where("companiesNeedingVerification.0.id", $company->id)
                    ->where("universitiesNeedingVerification.0.id", $university->id),
            );
    }

    public function testAdminDashboardDoesNotShowVerifiedCompaniesAndUniversities(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        Company::factory()->approved()->create();
        University::factory()->approved()->create();

        $this->actingAs($user)
            ->get("/admin/dashboard")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Dashboard")
                    ->has("companiesNeedingVerification", 0)
                    ->has("universitiesNeedingVerification", 0),
            );
    }

    public function testAdminDashboardOrdersPendingEntitiesOldestFirst(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);

        $company2 = Company::factory()->pending()->create(["created_at" => now()]);
        $company1 = Company::factory()->pending()->create(["created_at" => now()->subDays(2)]);

        $university2 = University::factory()->pending()->create(["created_at" => now()]);
        $university1 = University::factory()->pending()->create(["created_at" => now()->subDays(2)]);

        $this->actingAs($user)
            ->get("/admin/dashboard")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Dashboard")
                    ->has("companiesNeedingVerification", 2)
                    ->has("universitiesNeedingVerification", 2)
                    ->where("companiesNeedingVerification.0.id", $company1->id)
                    ->where("companiesNeedingVerification.1.id", $company2->id)
                    ->where("universitiesNeedingVerification.0.id", $university1->id)
                    ->where("universitiesNeedingVerification.1.id", $university2->id),
            );
    }

    public function testAdminCanAcceptCompanyVerification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $company = Company::factory()->pending()->create();

        $this->actingAs($admin)->post(route("admin.company.verify.accept", $company));

        Mail::assertSent(CompanyVerificationAcceptMail::class);

        $this->assertDatabaseHas("companies", [
            "id" => $company->id,
            "verification_status" => VerificationStatus::Verified,
        ]);
    }

    public function testAdminCanRejectCompanyVerification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);

        $company = Company::factory()->pending()->create();
        $rejectionReason = "Rejection Reason";
        $this->actingAs($admin)->post(route("admin.company.verify.reject", $company), ["rejection_reason" => $rejectionReason]);

        Mail::assertSent(CompanyVerificationRejectMail::class, fn($mail) => $mail->rejectionReason === $rejectionReason);

        $this->assertDatabaseHas("companies", [
            "id" => $company->id,
            "verification_status" => VerificationStatus::Rejected,
        ]);
    }

    public function testAdminCanAcceptUniversityVerification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $university = University::factory()->create([
            "verification_status" => VerificationStatus::Pending,
        ]);

        $this->actingAs($admin)->post(route("admin.university.verify.accept", $university));

        Mail::assertSent(UniversityVerificationAcceptMail::class);

        $this->assertDatabaseHas("universities", [
            "id" => $university->id,
            "verification_status" => VerificationStatus::Verified,
        ]);
    }

    public function testAdminCanRejectUniversityVerification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $university = University::factory()->create();

        $rejectionReason = "Rejection Reason";

        $this->actingAs($admin)->post(route("admin.university.verify.reject", $university), ["rejection_reason" => $rejectionReason]);

        Mail::assertSent(UniversityVerificationRejectMail::class, fn($mail) => $mail->rejectionReason === $rejectionReason);

        $this->assertDatabaseHas("universities", [
            "id" => $university->id,
            "verification_status" => VerificationStatus::Rejected,
        ]);
    }

    public function testThatVerificationOnAlreadyVerifiedEntityFails(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $company = Company::factory([
            "verification_status" => VerificationStatus::Verified,
        ])->create();

        $this->actingAs($admin)->post(route("admin.company.verify.accept", $company));

        Mail::assertNotSent(CompanyVerificationAcceptMail::class);
    }

    public function testThatRejectionOnAlreadyRejectedEntityFails(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $company = Company::factory()->create([
            "verification_status" => VerificationStatus::Rejected,
        ]);

        $this->actingAs($admin)->post(route("admin.company.verify.reject", $company));

        Mail::assertNotSent(CompanyVerificationAcceptMail::class);
    }
}
