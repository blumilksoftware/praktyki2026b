<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HideReviewTest extends TestCase
{
    use RefreshDatabase;

    public function testCompanyStaffCanHideReviewForTheirCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $review = Review::factory()->create(["company_id" => $company->id]);

        $this->actingAs($companyAdmin)
            ->patch(route("company.reviews.hide", $review))
            ->assertRedirect();

        $this->assertTrue($review->fresh()->hidden);
    }

    public function testCompanyStaffCannotHideReviewForAnotherCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $review = Review::factory()->create(["company_id" => $otherCompany->id]);

        $this->actingAs($companyAdmin)
            ->patch(route("company.reviews.hide", $review))
            ->assertForbidden();

        $this->assertFalse($review->fresh()->hidden);
    }
}
