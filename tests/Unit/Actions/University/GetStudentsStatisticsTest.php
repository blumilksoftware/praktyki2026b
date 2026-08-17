<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\GetStudentsStatistics;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Faculty;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetStudentsStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsTotalsAndBreakdownsForLinkedStudents(): void
    {
        $university = University::factory()->create();
        $engineering = Faculty::factory()->for($university)->create(["name" => "Engineering"]);
        $science = Faculty::factory()->for($university)->create(["name" => "Science"]);
        $computerScience = StudyField::factory()->for($engineering)->create(["name" => "Computer Science"]);
        $physics = StudyField::factory()->for($science)->create(["name" => "Physics"]);

        $engineeringStudent = User::factory()->create([
            "organization_id" => $university->id,
            "study_field_id" => $computerScience->id,
        ]);
        $scienceStudent = User::factory()->create([
            "organization_id" => $university->id,
            "study_field_id" => $physics->id,
        ]);

        Application::factory()->accepted()->create(["student_id" => $engineeringStudent->id]);
        Application::factory()->pending()->create(["student_id" => $engineeringStudent->id]);
        Application::factory()->accepted()->create(["student_id" => $scienceStudent->id]);

        User::factory()->create(["study_field_id" => $computerScience->id]);
        User::factory()->create([
            "organization_id" => $university->id,
            "role" => UserRole::UniversityAdmin,
        ]);

        $result = app(GetStudentsStatistics::class)->execute($university);

        $this->assertSame(2, $result["linkedStudents"]);
        $this->assertSame(3, $result["applicationsSubmitted"]);
        $this->assertSame(2, $result["acceptedPlacements"]);

        $this->assertEquals([
            [
                "facultyId" => $engineering->id,
                "facultyName" => "Engineering",
                "linkedStudents" => 1,
                "applicationsSubmitted" => 2,
                "acceptedPlacements" => 1,
            ],
            [
                "facultyId" => $science->id,
                "facultyName" => "Science",
                "linkedStudents" => 1,
                "applicationsSubmitted" => 1,
                "acceptedPlacements" => 1,
            ],
        ], $result["breakdownByFaculty"]->all());

        $this->assertInstanceOf(LengthAwarePaginator::class, $result["breakdownByField"]);
        $this->assertSame(2, $result["breakdownByField"]->total());
        $this->assertEquals([
            [
                "fieldId" => $computerScience->id,
                "fieldName" => "Computer Science",
                "linkedStudents" => 1,
                "applicationsSubmitted" => 2,
                "acceptedPlacements" => 1,
            ],
            [
                "fieldId" => $physics->id,
                "fieldName" => "Physics",
                "linkedStudents" => 1,
                "applicationsSubmitted" => 1,
                "acceptedPlacements" => 1,
            ],
        ], $result["breakdownByField"]->items());
    }

    public function testItFiltersApplicationsByDateRangeWithoutFilteringLinkedStudents(): void
    {
        $university = University::factory()->create();
        $student = User::factory()->create(["organization_id" => $university->id]);

        Application::factory()->accepted()->create([
            "student_id" => $student->id,
            "created_at" => "2026-01-15 12:00:00",
        ]);
        Application::factory()->pending()->create([
            "student_id" => $student->id,
            "created_at" => "2026-02-15 12:00:00",
        ]);
        Application::factory()->accepted()->create([
            "student_id" => $student->id,
            "created_at" => "2026-03-15 12:00:00",
        ]);

        $result = app(GetStudentsStatistics::class)->execute(
            $university,
            Carbon::parse("2026-02-01 00:00:00"),
            Carbon::parse("2026-02-28 23:59:59"),
        );

        $this->assertSame(1, $result["linkedStudents"]);
        $this->assertSame(1, $result["applicationsSubmitted"]);
        $this->assertSame(0, $result["acceptedPlacements"]);
    }

    public function testItListsFacultiesAndFieldsWithoutStudents(): void
    {
        $university = University::factory()->create();
        $faculty = Faculty::factory()->for($university)->create(["name" => "Engineering"]);
        StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);

        Faculty::factory()->for(University::factory()->create())->create(["name" => "Foreign Faculty"]);

        $result = app(GetStudentsStatistics::class)->execute($university);

        $this->assertEquals([
            [
                "facultyId" => $faculty->id,
                "facultyName" => "Engineering",
                "linkedStudents" => 0,
                "applicationsSubmitted" => 0,
                "acceptedPlacements" => 0,
            ],
        ], $result["breakdownByFaculty"]->all());

        $this->assertSame("Robotics", $result["breakdownByField"]->items()[0]["fieldName"]);
        $this->assertSame(0, $result["breakdownByField"]->items()[0]["linkedStudents"]);
        $this->assertSame(1, $result["breakdownByField"]->total());
    }

    public function testItKeepsStudentsWithoutStudyFieldInASeparateRow(): void
    {
        $university = University::factory()->create();
        $faculty = Faculty::factory()->for($university)->create(["name" => "Engineering"]);
        $robotics = StudyField::factory()->for($faculty)->create(["name" => "Robotics"]);

        User::factory()->create([
            "organization_id" => $university->id,
            "study_field_id" => $robotics->id,
        ]);
        User::factory()->create([
            "organization_id" => $university->id,
            "study_field_id" => null,
        ]);

        $result = app(GetStudentsStatistics::class)->execute($university);

        $this->assertSame(2, $result["linkedStudents"]);
        $this->assertEqualsCanonicalizing(
            [$faculty->id, null],
            array_column($result["breakdownByFaculty"]->all(), "facultyId"),
        );
        $this->assertEqualsCanonicalizing(
            [$robotics->id, null],
            array_column($result["breakdownByField"]->items(), "fieldId"),
        );
    }

    public function testItPaginatesBreakdownByField(): void
    {
        $university = University::factory()->create();
        $faculty = Faculty::factory()->for($university)->create();

        $studyFields = StudyField::factory()->for($faculty)->count(15)->create();

        foreach ($studyFields as $field) {
            User::factory()->create([
                "organization_id" => $university->id,
                "study_field_id" => $field->id,
            ]);
        }

        $result = app(GetStudentsStatistics::class)->execute(
            university: $university,
            fieldPage: 2,
            fieldPerPage: 10,
        );

        $this->assertInstanceOf(LengthAwarePaginator::class, $result["breakdownByField"]);
        $this->assertSame(15, $result["breakdownByField"]->total());
        $this->assertSame(2, $result["breakdownByField"]->currentPage());
        $this->assertCount(5, $result["breakdownByField"]->items());
    }
}
