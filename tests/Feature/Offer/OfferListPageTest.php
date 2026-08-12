<?php

declare(strict_types=1);

namespace Tests\Feature\Offer;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OfferListPageTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestSeesOffersAsGuestWithoutApplyPermission(): void
    {
        Offer::factory()->published()->create();

        $this->get(route("offers.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("isGuest", true)
                    ->where("canApply", false)
                    ->where("hasCv", false),
            );
    }

    public function testStudentWithCvCanApply(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => "cvs/student.pdf",
        ]);

        $this->actingAs($student)
            ->get(route("offers.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("isGuest", false)
                    ->where("canApply", true)
                    ->where("hasCv", true),
            );
    }

    public function testStudentWithoutCvIsAllowedToApplyButLacksCv(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "cv_path" => null,
        ]);

        $this->actingAs($student)
            ->get(route("offers.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("canApply", true)
                    ->where("hasCv", false),
            );
    }

    public function testCompanyUserIsNeitherGuestNorAbleToApply(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->get(route("offers.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("isGuest", false)
                    ->where("canApply", false)
                    ->where("hasCv", false),
            );
    }

    public function testUniversityUserIsNeitherGuestNorAbleToApply(): void
    {
        $university = University::factory()->create();
        $user = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->get(route("offers.index"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Offers")
                    ->where("isGuest", false)
                    ->where("canApply", false),
            );
    }
}
