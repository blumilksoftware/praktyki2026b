<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentProfilePagesTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotAccessStudentDashboardOrProfile(): void
    {
        $this->get(route("student.dashboard"))->assertRedirect(route("login"));
        $this->get(route("student.profile"))->assertRedirect(route("login"));
        $this->get(route("student.profile.edit"))->assertRedirect(route("login"));
        $this->get(route("student.settings"))->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotAccessStudentDashboardOrProfile(): void
    {
        $companyUser = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($companyUser)->get(route("student.dashboard"))->assertStatus(403);
        $this->actingAs($companyUser)->get(route("student.profile"))->assertStatus(403);
        $this->actingAs($companyUser)->get(route("student.profile.edit"))->assertStatus(403);
        $this->actingAs($companyUser)->get(route("student.settings"))->assertStatus(403);
    }

    public function testInactiveStudentCannotAccessStudentDashboardOrProfile(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $this->actingAs($student)->get(route("student.dashboard"))->assertStatus(403);
        $this->actingAs($student)->get(route("student.profile"))->assertStatus(403);
        $this->actingAs($student)->get(route("student.profile.edit"))->assertStatus(403);
        $this->actingAs($student)->get(route("student.settings"))->assertStatus(403);
    }

    public function testStudentCanSeeDashboard(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("student.dashboard"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Dashboard")
                    ->has("applications"),
            );
    }

    public function testStudentCanSeeProfileWithStudyFieldsAndEditProps(): void
    {
        $studyField = StudyField::factory()->create(["name" => "Informatyka"]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "first_name" => "John",
            "last_name" => "Doe",
            "email" => "john@example.com",
            "pending_email" => "new@example.com",
        ]);
        $student->preferredStudyFields()->sync([$studyField->id]);

        $this->actingAs($student)
            ->get(route("student.profile"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Profile")
                    ->where("user.first_name", "John")
                    ->where("user.last_name", "Doe")
                    ->where("user.email", "john@example.com")
                    ->where("user.pending_email", "new@example.com")
                    ->has("user.study_field_ids", 1)
                    ->where("user.study_field_ids.0", $studyField->id)
                    ->has("study_fields")
                    ->where("study_fields.0", [
                        "value" => $studyField->id,
                        "label" => "Informatyka",
                    ]),
            );
    }

    public function testStudentProfileSuggestsUniversityWhenDomainMatchesAndUnlinked(): void
    {
        $university = University::factory()->create([
            "domain" => "example.com",
            "verification_status" => VerificationStatus::Verified,
        ]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "john@example.com",
            "organization_id" => null,
        ]);

        $this->actingAs($student)
            ->get(route("student.profile"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Profile")
                    ->where("suggestedUniversity", [
                        "id" => $university->id,
                        "name" => $university->name,
                    ])
                    ->where("universityOrganization", null),
            );
    }

    public function testStudentProfileHasNoSuggestionWhenAlreadyLinked(): void
    {
        University::factory()->create([
            "domain" => "example.com",
            "verification_status" => VerificationStatus::Verified,
        ]);
        $linkedUniversity = University::factory()->create([
            "verification_status" => VerificationStatus::Verified,
        ]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "john@example.com",
            "organization_id" => $linkedUniversity->id,
        ]);

        $this->actingAs($student)
            ->get(route("student.profile"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Profile")
                    ->where("suggestedUniversity", null)
                    ->where("universityOrganization", [
                        "id" => $linkedUniversity->id,
                        "name" => $linkedUniversity->name,
                    ]),
            );
    }

    public function testStudentProfileHasNoSuggestionWhenNoDomainMatch(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "john@unrelated.com",
            "organization_id" => null,
        ]);

        $this->actingAs($student)
            ->get(route("student.profile"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Profile")
                    ->where("suggestedUniversity", null)
                    ->where("universityOrganization", null),
            );
    }

    public function testStudentCanSeeProfileEditPage(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("student.profile.edit"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/ProfileEdit")
                    ->has("user")
                    ->has("study_fields"),
            );
    }

    public function testStudentCanSeeSettingsPage(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "john@example.com",
            "pending_email" => "new@example.com",
        ]);

        $this->actingAs($student)
            ->get(route("student.settings"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/Settings")
                    ->where("email", "john@example.com")
                    ->where("pendingEmail", "new@example.com"),
            );
    }

    public function testSettingsRedirectSendsStudentToStudentSettings(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $this->actingAs($student)
            ->get(route("settings"))
            ->assertRedirect(route("student.settings"));
    }
}
