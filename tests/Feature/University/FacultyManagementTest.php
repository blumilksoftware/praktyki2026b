<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Faculty;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FacultyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotOpenTheFacultiesPage(): void
    {
        $this->get("/university/faculties")->assertRedirect(route("login"));
    }

    public function testAdminSeesOwnFacultiesWithUsageCounts(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $faculty = Faculty::factory()->for($university)->create(["name" => "Faculty of Engineering"]);
        $studyField = StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);

        User::factory()->create([
            "role" => UserRole::Student,
            "organization_id" => $university->id,
            "study_field_id" => $studyField->id,
        ]);
        User::factory()->create([
            "role" => UserRole::Student,
            "study_field_id" => $studyField->id,
        ]);

        Offer::factory()->published()->create()->studyFields()->attach($studyField);
        Offer::factory()->draft()->create()->studyFields()->attach($studyField);

        Faculty::factory()->create(["name" => "Faculty of Somewhere Else"]);

        $this->actingAs($user)
            ->get("/university/faculties")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/Faculties")
                    ->has("faculties", 1)
                    ->where("faculties.0.name", "Faculty of Engineering")
                    ->where("faculties.0.study_fields.0.name", "Robotics")
                    ->where("faculties.0.study_fields.0.students_count", 1)
                    ->where("faculties.0.study_fields.0.offers_count", 1),
            );
    }

    public function testAdminCanAddRenameAndAddFieldsToAFaculty(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->post("/university/faculties", ["name" => "Faculty of Engineering"])
            ->assertRedirect();

        $faculty = $university->faculties()->firstOrFail();

        $this->actingAs($user)
            ->patch("/university/faculties/{$faculty->id}", ["name" => "Faculty of Applied Engineering"])
            ->assertRedirect();

        $this->actingAs($user)
            ->post("/university/faculties/{$faculty->id}/study-fields", ["name" => "Robotics"])
            ->assertRedirect();

        $this->assertDatabaseHas("faculties", ["id" => $faculty->id, "name" => "Faculty of Applied Engineering"]);
        $this->assertDatabaseHas("study_fields", ["faculty_id" => $faculty->id, "name" => "Robotics"]);
    }

    public function testFacultyNamesMustBeUniqueWithinTheUniversity(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        Faculty::factory()->for($university)->create(["name" => "Faculty of Engineering"]);

        $this->actingAs($user)
            ->post("/university/faculties", ["name" => "Faculty of Engineering"])
            ->assertInvalid("name");
    }

    public function testTheSameFacultyNameIsAllowedInAnotherUniversity(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        Faculty::factory()->create(["name" => "Faculty of Engineering"]);

        $this->actingAs($user)
            ->post("/university/faculties", ["name" => "Faculty of Engineering"])
            ->assertValid();
    }

    public function testAdminCannotTouchAnotherUniversityFaculty(): void
    {
        $user = $this->makeUniversityAdmin(University::factory()->approved()->create());
        $foreignFaculty = Faculty::factory()->create();

        $this->actingAs($user)
            ->patch("/university/faculties/{$foreignFaculty->id}", ["name" => "Renamed"])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete("/university/faculties/{$foreignFaculty->id}")
            ->assertForbidden();
    }

    public function testUnusedFacultyIsDeletedWithoutAReplacement(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $faculty = Faculty::factory()->for($university)->create();
        StudyField::factory()->for($faculty)->create();

        $this->actingAs($user)
            ->delete("/university/faculties/{$faculty->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing("faculties", ["id" => $faculty->id]);
    }

    public function testDeletingAFacultyInUseRequiresAReplacement(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $faculty = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        User::factory()->create(["study_field_id" => $studyField->id]);

        $this->actingAs($user)
            ->delete("/university/faculties/{$faculty->id}")
            ->assertInvalid("reassign_to");

        $this->assertDatabaseHas("faculties", ["id" => $faculty->id]);
    }

    public function testDeletingAFacultyInUseMovesItsFieldsToTheReplacement(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $faculty = Faculty::factory()->for($university)->create();
        $replacement = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        $student = User::factory()->create(["study_field_id" => $studyField->id]);

        $this->actingAs($user)
            ->delete("/university/faculties/{$faculty->id}", ["reassign_to" => $replacement->id])
            ->assertRedirect();

        $this->assertDatabaseMissing("faculties", ["id" => $faculty->id]);
        $this->assertSame($replacement->id, $studyField->fresh()->faculty_id);
        $this->assertSame($studyField->id, $student->fresh()->study_field_id);
    }

    public function testAFacultyFromAnotherUniversityIsNotAValidReplacement(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $faculty = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        User::factory()->create(["study_field_id" => $studyField->id]);
        $foreignFaculty = Faculty::factory()->create();

        $this->actingAs($user)
            ->delete("/university/faculties/{$faculty->id}", ["reassign_to" => $foreignFaculty->id])
            ->assertInvalid("reassign_to");
    }

    public function testDeletingAFieldUsedByAnOfferRequiresAReplacement(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);
        $faculty = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();
        $replacement = StudyField::factory()->for($faculty)->create();
        Offer::factory()->create()->studyFields()->attach($studyField);

        $this->actingAs($user)
            ->delete("/university/study-fields/{$studyField->id}")
            ->assertInvalid("reassign_to");

        $this->actingAs($user)
            ->delete("/university/study-fields/{$studyField->id}", ["reassign_to" => $replacement->id])
            ->assertRedirect();

        $this->assertDatabaseMissing("study_fields", ["id" => $studyField->id]);
    }

    public function testAdminCannotTouchAnotherUniversityStudyField(): void
    {
        $user = $this->makeUniversityAdmin(University::factory()->approved()->create());
        $foreignField = StudyField::factory()->create();

        $this->actingAs($user)
            ->patch("/university/study-fields/{$foreignField->id}", ["name" => "Renamed"])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete("/university/study-fields/{$foreignField->id}")
            ->assertForbidden();
    }

    private function makeUniversityAdmin(University $university): User
    {
        return User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
        ]);
    }
}
