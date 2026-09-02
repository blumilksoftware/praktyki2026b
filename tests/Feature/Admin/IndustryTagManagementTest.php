<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\IndustryTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustryTagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotManageIndustryTags(): void
    {
        $tag = IndustryTag::factory()->create();

        $this->post("/admin/industry-tags", ["name" => "IT"])->assertStatus(401);
        $this->patch("/admin/industry-tags/{$tag->id}", ["name" => "Marketing"])->assertStatus(401);
        $this->delete("/admin/industry-tags/{$tag->id}")->assertStatus(401);
    }

    public function testNonSuperAdminCannotManageIndustryTags(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $tag = IndustryTag::factory()->create();

        $this->actingAs($admin)->post("/admin/industry-tags", ["name" => "IT"])->assertStatus(403);
        $this->actingAs($admin)->patch("/admin/industry-tags/{$tag->id}", ["name" => "Marketing"])->assertStatus(403);
        $this->actingAs($admin)->delete("/admin/industry-tags/{$tag->id}")->assertStatus(403);
    }

    public function testSuperAdminCanCreateAnIndustryTag(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->post("/admin/industry-tags", ["name" => "IT"])
            ->assertRedirect();

        $tag = IndustryTag::query()->where("name", "IT")->firstOrFail();

        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $tag->id,
            "causer_id" => $admin->id,
            "description" => "industry_tag_created",
        ]);
    }

    public function testIndustryTagNameMustBeUnique(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        IndustryTag::factory()->create(["name" => "IT"]);

        $this->actingAs($admin)
            ->post("/admin/industry-tags", ["name" => "it"])
            ->assertInvalid("name");
    }

    public function testSuperAdminCanRenameAnIndustryTag(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $tag = IndustryTag::factory()->create(["name" => "IT"]);

        $this->actingAs($admin)
            ->patch("/admin/industry-tags/{$tag->id}", ["name" => "Marketing"])
            ->assertRedirect();

        $this->assertDatabaseHas("industry_tags", ["id" => $tag->id, "name" => "Marketing"]);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $tag->id,
            "causer_id" => $admin->id,
            "description" => "industry_tag_updated",
        ]);
    }

    public function testSuperAdminCanDeleteAnIndustryTag(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $tag = IndustryTag::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/industry-tags/{$tag->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing("industry_tags", ["id" => $tag->id]);
        $this->assertDatabaseHas("activity_log", [
            "causer_id" => $admin->id,
            "description" => "industry_tag_deleted",
        ]);
    }

    public function testCompanyCanSaveTagsThatAreNotInTheCanonicalList(): void
    {
        IndustryTag::factory()->create(["name" => "IT"]);
        $company = Company::factory()->approved()->create();
        $companyAdmin = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);

        $this->actingAs($companyAdmin)
            ->patch("/company/profile", [
                "tags" => ["Not A Real Tag"],
                "phone" => "123456789",
                "street" => "Main 1",
                "postalCode" => "00-000",
                "city" => "Warszawa",
                "nip" => "1234567890",
            ])
            ->assertValid()
            ->assertRedirect("/company/profile");

        $this->assertSame(["Not A Real Tag"], $company->fresh()->tags);
    }
}
