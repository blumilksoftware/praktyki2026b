<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Student;

use App\Actions\Student\GetStudentApplicationsAction;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetStudentApplicationsActionTest extends TestCase
{
    use RefreshDatabase;

    private GetStudentApplicationsAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetStudentApplicationsAction();
    }

    public function testReturnsStudentApplicationsHistoryIncludingSoftDeletedOffers(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();

        $company = Company::factory()->approved()->create(["name" => "TechCorp"]);
        $offer1 = Offer::factory()->create(["company_id" => $company->id, "title" => "Backend Developer"]);
        $offer2 = Offer::factory()->create(["company_id" => $company->id, "title" => "Frontend Developer"]);

        $app1 = Application::factory()->create([
            "student_id" => $student->id,
            "offer_id" => $offer1->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $app2 = Application::factory()->create([
            "student_id" => $student->id,
            "offer_id" => $offer2->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        Application::factory()->create([
            "student_id" => $otherStudent->id,
            "offer_id" => $offer1->id,
        ]);

        $offer2->delete();

        $history = $this->action->execute($student);

        $this->assertCount(2, $history);

        $firstItem = $history->firstWhere("id", $app1->id);
        $this->assertNotNull($firstItem);
        $this->assertEquals($offer1->id, $firstItem["offer_id"]);
        $this->assertEquals("Backend Developer", $firstItem["offer_title"]);
        $this->assertEquals("TechCorp", $firstItem["company_name"]);
        $this->assertEquals("pending", $firstItem["status"]);
        $this->assertEquals($app1->created_at->toIso8601String(), $firstItem["date_applied"]);

        $secondItem = $history->firstWhere("id", $app2->id);
        $this->assertNotNull($secondItem);
        $this->assertEquals("Frontend Developer", $secondItem["offer_title"]);
        $this->assertEquals("TechCorp", $secondItem["company_name"]);
        $this->assertEquals("accepted", $secondItem["status"]);
    }
}
