<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersPageTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminUsersPageListsAllUsers(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        User::factory()->create(["role" => UserRole::Student]);
        User::factory()->create(["role" => UserRole::CompanyAdmin]);

        $this->actingAs($admin)
            ->get("/admin/users")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Users")
                    ->has("users.data", 3)
                    ->has("roles"),
            );
    }

    public function testAdminUsersPageFiltersBySearch(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        User::factory()->create(["role" => UserRole::Student, "email" => "findme@example.com"]);
        User::factory()->create(["role" => UserRole::Student, "email" => "someoneelse@example.com"]);

        $this->actingAs($admin)
            ->get("/admin/users?search=findme")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Users")
                    ->has("users.data", 1)
                    ->where("users.data.0.email", "findme@example.com"),
            );
    }

    public function testAdminUsersPageFiltersByRole(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        User::factory()->create(["role" => UserRole::Student]);
        User::factory()->create(["role" => UserRole::CompanyAdmin]);

        $this->actingAs($admin)
            ->get("/admin/users?role=" . UserRole::CompanyAdmin->value)
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Admin/Users")
                    ->has("users.data", 1)
                    ->where("users.data.0.role", UserRole::CompanyAdmin->value),
            );
    }
}
