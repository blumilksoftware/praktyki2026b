<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentsStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function testDashboardReturnsStatisticsFilteredByDateRange(): void
    {
        $university = University::factory()->approved()->create(["domain" => "example.edu"]);
        $faculty = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();

        $admin = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $student = User::factory()->create([
            "email" => "student@example.edu",
            "study_field_id" => $studyField->id,
        ]);

        Application::factory()->accepted()->create([
            "student_id" => $student->id,
            "created_at" => "2026-01-31 23:59:59",
        ]);
        Application::factory()->accepted()->create([
            "student_id" => $student->id,
            "created_at" => "2026-02-28 23:59:59",
        ]);

        $this->actingAs($admin)
            ->get(route("university.dashboard", ["from" => "2026-02-01", "to" => "2026-02-28"]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component("University/Dashboard")
                ->where("data.linkedStudents", 1)
                ->where("data.applicationsSubmitted", 1)
                ->where("data.acceptedPlacements", 1)
                ->has("data.breakdownByFaculty.data", 1)
                ->where("data.breakdownByFaculty.data.0.facultyId", $faculty->id)
                ->where("data.breakdownByFaculty.data.0.linkedStudents", 1)
                ->has("data.breakdownByField.data", 1)
                ->where("data.breakdownByField.data.0.fieldId", $studyField->id)
                ->where("data.breakdownByField.data.0.linkedStudents", 1)
                ->where("filters.from", "2026-02-01")
                ->where("filters.to", "2026-02-28"));
    }

    public function testDashboardRejectsAnInvalidDateRange(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $this->actingAs($admin)
            ->get(route("university.dashboard", ["from" => "2026-03-01", "to" => "2026-02-01"]))
            ->assertRedirect()
            ->assertSessionHasErrors("to");
    }

    public function testDashboardSearchesBreakdownByFieldName(): void
    {
        $university = University::factory()->approved()->create(["domain" => "example.edu"]);
        $faculty = Faculty::factory()->for($university)->create();

        $matchingField = StudyField::factory()->for($faculty)->create(["name" => "Computer Science"]);
        $otherField = StudyField::factory()->for($faculty)->create(["name" => "Philosophy"]);

        $admin = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        User::factory()->create([
            "email" => "cs-student@example.edu",
            "study_field_id" => $matchingField->id,
        ]);
        User::factory()->create([
            "email" => "phil-student@example.edu",
            "study_field_id" => $otherField->id,
        ]);

        $this->actingAs($admin)
            ->get(route("university.dashboard", ["fieldSearch" => "computer"]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component("University/Dashboard")
                ->has("data.breakdownByField.data", 1)
                ->where("data.breakdownByField.data.0.fieldId", $matchingField->id)
                ->where("filters.fieldSearch", "computer"));
    }

    public function testDashboardSortsBreakdownByFacultyDescending(): void
    {
        $university = University::factory()->approved()->create(["domain" => "example.edu"]);

        $facultyA = Faculty::factory()->for($university)->create(["name" => "Alpha Faculty"]);
        $facultyZ = Faculty::factory()->for($university)->create(["name" => "Zeta Faculty"]);

        $fieldA = StudyField::factory()->for($facultyA)->create();
        $fieldZ = StudyField::factory()->for($facultyZ)->create();

        $admin = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        User::factory()->create([
            "email" => "student-a@example.edu",
            "study_field_id" => $fieldA->id,
        ]);
        User::factory()->create([
            "email" => "student-z@example.edu",
            "study_field_id" => $fieldZ->id,
        ]);

        $this->actingAs($admin)
            ->get(route("university.dashboard", [
                "facultySort" => "facultyName",
                "facultyDirection" => "desc",
            ]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component("University/Dashboard")
                ->has("data.breakdownByFaculty.data", 2)
                ->where("data.breakdownByFaculty.data.0.facultyId", $facultyZ->id)
                ->where("data.breakdownByFaculty.data.1.facultyId", $facultyA->id));
    }

    public function testDashboardRejectsInvalidSortColumn(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);

        $this->actingAs($admin)
            ->get(route("university.dashboard", ["fieldSort" => "not-a-real-column"]))
            ->assertRedirect()
            ->assertSessionHasErrors("fieldSort");
    }
}
