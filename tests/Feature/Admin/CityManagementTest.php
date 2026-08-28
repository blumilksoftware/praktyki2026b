<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotManageCities(): void
    {
        $city = City::factory()->create();

        $this->post("/admin/cities", ["name" => "Warszawa"])->assertStatus(401);
        $this->patch("/admin/cities/{$city->id}", ["name" => "Kraków"])->assertStatus(401);
        $this->delete("/admin/cities/{$city->id}")->assertStatus(401);
    }

    public function testNonSuperAdminCannotManageCities(): void
    {
        $admin = User::factory()->create(["role" => UserRole::CompanyAdmin]);
        $city = City::factory()->create();

        $this->actingAs($admin)->post("/admin/cities", ["name" => "Warszawa"])->assertStatus(403);
        $this->actingAs($admin)->patch("/admin/cities/{$city->id}", ["name" => "Kraków"])->assertStatus(403);
        $this->actingAs($admin)->delete("/admin/cities/{$city->id}")->assertStatus(403);
    }

    public function testSuperAdminCanCreateACity(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->post("/admin/cities", ["name" => "Warszawa"])
            ->assertRedirect();

        $city = City::query()->where("name", "Warszawa")->firstOrFail();

        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $city->id,
            "causer_id" => $admin->id,
            "description" => "city_created",
        ]);
    }

    public function testCityNameMustBeUnique(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        City::factory()->create(["name" => "Warszawa"]);

        $this->actingAs($admin)
            ->post("/admin/cities", ["name" => "warszawa"])
            ->assertInvalid("name");
    }

    public function testSuperAdminCanRenameACity(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $city = City::factory()->create(["name" => "Warszawa"]);

        $this->actingAs($admin)
            ->patch("/admin/cities/{$city->id}", ["name" => "Kraków"])
            ->assertRedirect();

        $this->assertDatabaseHas("cities", ["id" => $city->id, "name" => "Kraków"]);
        $this->assertDatabaseHas("activity_log", [
            "subject_id" => $city->id,
            "causer_id" => $admin->id,
            "description" => "city_updated",
        ]);
    }

    public function testSuperAdminCanDeleteACity(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $city = City::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/cities/{$city->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing("cities", ["id" => $city->id]);
        $this->assertDatabaseHas("activity_log", [
            "causer_id" => $admin->id,
            "description" => "city_deleted",
        ]);
    }
}
