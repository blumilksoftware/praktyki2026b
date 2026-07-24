<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentApplicationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAccessStudentApplications(): void
    {
        $this->get(route("student.applications"))->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotAccessStudentApplications(): void
    {
        $companyUser = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($companyUser)->get(route("student.applications"))->assertStatus(403);
    }

    public function testInactiveStudentCannotAccessStudentApplications(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $this->actingAs($student)->get(route("student.applications"))->assertStatus(403);
    }

    public function testStudentCanSeeApplicationsPage(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("student.applications"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Applications")
                    ->has("applications"),
            );
    }
}
