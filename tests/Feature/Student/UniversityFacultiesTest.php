<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityFacultiesTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotListFaculties(): void
    {
        $university = University::factory()->approved()->create();

        $this->getJson(route("student.universities.faculties", $university))->assertUnauthorized();
    }

    public function testStudentGetsFacultiesWithTheirFieldsOfStudy(): void
    {
        $university = University::factory()->approved()->create();
        $faculty = Faculty::factory()->for($university)->create(["name" => "Faculty of Engineering"]);
        StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);

        $this->actingAs($this->makeStudent())
            ->getJson(route("student.universities.faculties", $university))
            ->assertOk()
            ->assertJsonPath("faculties.0.name", "Faculty of Engineering")
            ->assertJsonPath("faculties.0.study_fields.0.name", "Robotics");
    }

    public function testUnverifiedUniversityIsNotExposed(): void
    {
        $university = University::factory()->create();

        $this->actingAs($this->makeStudent())
            ->getJson(route("student.universities.faculties", $university))
            ->assertNotFound();
    }

    private function makeStudent(): User
    {
        return User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
    }
}
