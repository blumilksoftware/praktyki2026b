<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\DeleteFaculty;
use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteFacultyTest extends TestCase
{
    use RefreshDatabase;

    public function testItDeletesAFacultyTogetherWithItsFields(): void
    {
        $faculty = Faculty::factory()->create();
        $studyField = StudyField::factory()->for($faculty)->create();

        app(DeleteFaculty::class)->execute($faculty);

        $this->assertDatabaseMissing("faculties", ["id" => $faculty->id]);
        $this->assertDatabaseMissing("study_fields", ["id" => $studyField->id]);
    }

    public function testItMovesFieldsToTheReplacementFaculty(): void
    {
        $university = University::factory()->create();
        $faculty = Faculty::factory()->for($university)->create();
        $replacement = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);
        $student = User::factory()->create(["study_field_id" => $studyField->id]);

        app(DeleteFaculty::class)->execute($faculty, $replacement);

        $this->assertDatabaseMissing("faculties", ["id" => $faculty->id]);
        $this->assertSame($replacement->id, $studyField->fresh()->faculty_id);
        $this->assertSame($studyField->id, $student->fresh()->study_field_id);
    }

    public function testItMergesFieldsThatTheReplacementFacultyAlreadyHas(): void
    {
        $university = University::factory()->create();
        $faculty = Faculty::factory()->for($university)->create();
        $replacement = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);
        $existing = StudyField::factory()->for($replacement)->create(["name" => "Robotics"]);
        $student = User::factory()->create(["study_field_id" => $studyField->id]);

        app(DeleteFaculty::class)->execute($faculty, $replacement);

        $this->assertDatabaseMissing("study_fields", ["id" => $studyField->id]);
        $this->assertSame($existing->id, $student->fresh()->study_field_id);
        $this->assertSame(1, $replacement->studyFields()->count());
    }
}
