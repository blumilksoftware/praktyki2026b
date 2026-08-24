<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnhideReviewTest extends TestCase
{
    use RefreshDatabase;

    public function testCompanyStaffCanUnhideReviewForTheirCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $review = Review::factory()->hidden()->create(["company_id" => $company->id]);

        $this->actingAs($companyAdmin)
            ->patch(route("company.reviews.unhide", $review))
            ->assertRedirect();

        $this->assertFalse($review->fresh()->hidden);
    }

    public function testCompanyStaffCannotUnhideReviewForAnotherCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $review = Review::factory()->hidden()->create(["company_id" => $otherCompany->id]);

        $this->actingAs($companyAdmin)
            ->patch(route("company.reviews.unhide", $review))
            ->assertForbidden();

        $this->assertTrue($review->fresh()->hidden);
    }
}
