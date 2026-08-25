<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testOwnProfilePageShowsReviewsIncludingHiddenAndAllowsModeration(): void
    {
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        Review::factory()->create(["company_id" => $company->id]);
        Review::factory()->hidden()->create(["company_id" => $company->id]);

        $this->actingAs($companyAdmin)
            ->get(route("company.profile"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Profile/Show")
                    ->has("company.reviews.items", 2)
                    ->where("company.reviews.canModerate", true),
            );
    }
}
