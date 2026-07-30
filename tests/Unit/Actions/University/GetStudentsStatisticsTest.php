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

    public function testItReturnsTotalsAndBreakdownsForDomainAndManuallyLinkedStudents(): void
    {
        $university = University::factory()->create(["domain" => "example.edu"]);
        $engineering = Faculty::factory()->for($university)->create(["name" => "Engineering"]);
        $science = Faculty::factory()->for($university)->create(["name" => "Science"]);
        $computerScience = StudyField::factory()->for($engineering)->create(["name" => "Computer Science"]);
        $physics = StudyField::factory()->for($science)->create(["name" => "Physics"]);

        $domainStudent = User::factory()->create([
            "email" => "domain.student@example.edu",
            "study_field" => $computerScience->id,
        ]);
        $manuallyLinkedStudent = User::factory()->create([
            "email" => "student@elsewhere.test",
            "organization_id" => $university->id,
            "study_field" => $physics->id,
        ]);

        Application::factory()->accepted()->create(["student_id" => $domainStudent->id]);
        Application::factory()->pending()->create(["student_id" => $domainStudent->id]);
        Application::factory()->accepted()->create(["student_id" => $manuallyLinkedStudent->id]);

        User::factory()->create(["email" => "unrelated@elsewhere.test"]);
        User::factory()->create([
            "email" => "admin@example.edu",
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
        $university = University::factory()->create(["domain" => "example.edu"]);
        $student = User::factory()->create(["email" => "student@example.edu"]);

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

    public function testItPaginatesBreakdownByField(): void
    {
        $university = University::factory()->create(["domain" => "example.edu"]);
        $faculty = Faculty::factory()->for($university)->create();

        // Создаем 15 направлений
        $studyFields = StudyField::factory()->for($faculty)->count(15)->create();

        // Создаем по студенту для каждого направления (привязаны к университету по домену email)
        foreach ($studyFields as $index => $field) {
            User::factory()->create([
                "email" => "student{$index}@example.edu",
                "study_field" => $field->id,
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
