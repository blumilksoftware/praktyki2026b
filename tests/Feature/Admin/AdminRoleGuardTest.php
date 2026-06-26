<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleGuardTest extends TestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedUserReceives401(): void
    {
        $this->get(route("admin.dashboard"))->assertStatus(401);
    }

    public function testStudentReceives403(): void
    {
        $user = User::factory()->create(["role" => UserRole::Student]);

        $this->actingAs($user)->get(route("admin.dashboard"))->assertStatus(403);
    }

    public function testCompanyAdminReceives403(): void
    {
        $user = User::factory()->create(["role" => UserRole::CompanyAdmin]);

        $this->actingAs($user)->get(route("admin.dashboard"))->assertStatus(403);
    }

    public function testUniversityAdminReceives403(): void
    {
        $user = User::factory()->create(["role" => UserRole::UniversityAdmin]);

        $this->actingAs($user)->get(route("admin.dashboard"))->assertStatus(403);
    }

    public function testSuperAdminCanAccessAdminRoutes(): void
    {
        $user = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $this->actingAs($user)->get(route("admin.dashboard"))->assertOk();
    }
}
