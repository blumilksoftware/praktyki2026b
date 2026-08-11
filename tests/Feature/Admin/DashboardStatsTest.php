<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Offer;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminDashboardShowsRealStats(): void
    {
        $admin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);

        User::factory()->count(2)->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);
        User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $offerOwner = Company::factory()->approved()->create();
        Company::factory()->approved()->create();
        Company::factory()->pending()->create();

        University::factory()->approved()->create();
        University::factory()->pending()->create();

        Offer::factory()->for($offerOwner)->count(3)->published()->create();
        Offer::factory()->for($offerOwner)->draft()->create();

        $this->actingAs($admin)
            ->get("/admin/dashboard")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Dashboard")
                    ->where("stats.activeStudents", 2)
                    ->where("stats.approvedCompanies", 2)
                    ->where("stats.approvedUniversities", 1)
                    ->where("stats.activeOffers", 3)
                    ->where("pendingVerifications", 2)
                    ->where("totalVerifications", 5),
            );
    }
}
