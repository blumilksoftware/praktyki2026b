<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Enums\UserRole;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteReviewTest extends TestCase
{
    use RefreshDatabase;

    public function testSuperAdminCanDeleteReview(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $review = Review::factory()->create();

        $this->actingAs($admin)
            ->delete(route("admin.reviews.destroy", $review))
            ->assertRedirect();

        $this->assertModelMissing($review);
    }

    public function testNonAdminCannotDeleteReview(): void
    {
        $companyAdmin = User::factory()->companyAdmin()->create();
        $review = Review::factory()->create();

        $this->actingAs($companyAdmin)
            ->delete(route("admin.reviews.destroy", $review))
            ->assertForbidden();

        $this->assertModelExists($review);
    }
}
