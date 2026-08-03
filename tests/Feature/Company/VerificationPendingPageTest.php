<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VerificationPendingPageTest extends TestCase
{
    use RefreshDatabase;

    public function testActiveUserWithPendingCompanyCanCreateDraftOffer(): void
    {
        $company = Company::factory()->pending()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/verification/pending");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->component("Auth/VerificationPending")
            ->where("canCreateDraftOffer", true));
    }

    public function testActiveUserWithVerifiedCompanyCannotCreateDraftOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/verification/pending");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->where("canCreateDraftOffer", false));
    }

    public function testActiveUserWithRejectedCompanyCannotCreateDraftOffer(): void
    {
        $company = Company::factory()->rejected()->create();
        $user = $this->makeCompanyAdmin($company);

        $response = $this->actingAs($user)->get("/company/verification/pending");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->where("canCreateDraftOffer", false));
    }

    public function testPendingUserAccountCannotCreateDraftOffer(): void
    {
        $company = Company::factory()->pending()->create();
        $user = User::factory()->pendingCompanyAdmin()->create([
            "organization_id" => $company->id,
        ]);

        $response = $this->actingAs($user)->get("/company/verification/pending");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->where("canCreateDraftOffer", false));
    }

    public function testUniversityVerificationPendingPageHasNoDraftOfferProp(): void
    {
        $university = University::factory()->pending()->create();
        $user = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $response = $this->actingAs($user)->get("/university/verification/pending");

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->component("Auth/VerificationPending")
            ->missing("canCreateDraftOffer"));
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
            "first_name" => null,
            "last_name" => null,
        ]);
    }
}
