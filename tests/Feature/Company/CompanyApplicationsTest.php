<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyApplicationsTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config("filesystems.default", "local");
        Storage::fake($this->disk);
    }

    public function testGuestCannotAccessApplicationsListOrDownloadCv(): void
    {
        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);

        $this->get(route("company.applications"))->assertRedirect(route("login"));
        $this->get(route("company.applications.cv", $application))->assertRedirect(route("login"));
    }

    public function testNonCompanyAdminRoleCannotAccessApplicationsListOrDownloadCv(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $company = Company::factory()->approved()->create();
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);

        $this->actingAs($student)->get(route("company.applications"))->assertStatus(403);
        $this->actingAs($student)->get(route("company.applications.cv", $application))->assertStatus(403);
    }

    public function testUnverifiedCompanyCannotAccessApplicationsListOrDownloadCv(): void
    {
        $company = Company::factory()->pending()->create();
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create(["offer_id" => $offer->id]);

        $this->actingAs($user)->get(route("company.applications"))->assertRedirect(route("company.verification.pending"));
        $this->actingAs($user)->get(route("company.applications.cv", $application))->assertRedirect(route("company.verification.pending"));
    }

    public function testCompanyAdminCanAccessApplicationsList(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create(["company_id" => $company->id]);

        $student = User::factory()->create([
            "first_name" => "John",
            "last_name" => "Doe",
            "university" => "Harvard",
        ]);

        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "status" => ApplicationStatus::Pending,
            "cv_path" => "applications/cvs/test_snapshot.pdf",
        ]);

        $this->actingAs($user)
            ->get(route("company.applications"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Applications")
                    ->has("applications.data", 1)
                    ->where("applications.data.0.id", $application->id)
                    ->where("applications.data.0.student_name", "John Doe")
                    ->where("applications.data.0.university", "Harvard")
                    ->where("applications.data.0.status", "pending")
                    ->where("applications.data.0.offer_title", $offer->title)
                    ->where("applications.data.0.cv_url", route("company.applications.cv", $application))
                    ->has("offers", 1)
                    ->where("offers.0.id", $offer->id)
                    ->where("offers.0.title", $offer->title)
                    ->where("filters.offer", null)
                    ->where("filters.status", null),
            );
    }

    public function testCompanyAdminCanFilterApplicationsByStatusAndOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer1 = Offer::factory()->create(["company_id" => $company->id, "title" => "Offer A"]);
        $offer2 = Offer::factory()->create(["company_id" => $company->id, "title" => "Offer B"]);

        $student1 = User::factory()->create(["first_name" => "Student", "last_name" => "One"]);
        $student2 = User::factory()->create(["first_name" => "Student", "last_name" => "Two"]);

        $app1 = Application::factory()->create([
            "offer_id" => $offer1->id,
            "student_id" => $student1->id,
            "status" => ApplicationStatus::Pending,
        ]);

        $app2 = Application::factory()->create([
            "offer_id" => $offer2->id,
            "student_id" => $student2->id,
            "status" => ApplicationStatus::Accepted,
        ]);

        $this->actingAs($user)
            ->get(route("company.applications", ["status" => "accepted"]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Applications")
                    ->has("applications.data", 1)
                    ->where("applications.data.0.id", $app2->id)
                    ->where("filters.status", "accepted"),
            );

        $this->actingAs($user)
            ->get(route("company.applications", ["offer" => $offer1->id]))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Applications")
                    ->has("applications.data", 1)
                    ->where("applications.data.0.id", $app1->id)
                    ->where("filters.offer", $offer1->id),
            );
    }

    public function testCompanyAdminCanDownloadCvOfStudentWhoAppliedToTheirOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $student = User::factory()->create([
            "first_name" => "Jane",
            "last_name" => "Smith",
        ]);

        $cvPath = "applications/cvs/jane_smith_snapshot.pdf";
        Storage::disk($this->disk)->put($cvPath, "Jane Smith CV Content");

        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "cv_path" => $cvPath,
        ]);

        $response = $this->actingAs($user)->get(route("company.applications.cv", $application));

        $response->assertOk();
        $response->assertHeader("Content-Disposition", "attachment; filename=Jane_Smith_CV.pdf");
        $this->assertEquals("Jane Smith CV Content", $response->streamedContent());
    }

    public function testCompanyAdminCannotDownloadCvOfStudentWhoAppliedToAnotherCompanysOffer(): void
    {
        $company1 = Company::factory()->approved()->create();
        $user1 = $this->makeCompanyAdmin($company1);

        $company2 = Company::factory()->approved()->create();
        $offer2 = Offer::factory()->create(["company_id" => $company2->id]);

        $cvPath = "applications/cvs/other_student.pdf";
        Storage::disk($this->disk)->put($cvPath, "Other Student CV Content");

        $application = Application::factory()->create([
            "offer_id" => $offer2->id,
            "cv_path" => $cvPath,
        ]);

        $this->actingAs($user1)
            ->get(route("company.applications.cv", $application))
            ->assertStatus(403);
    }

    public function testDownloadCvReturns404IfCvPathIsNull(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "cv_path" => null,
        ]);

        $this->actingAs($user)
            ->get(route("company.applications.cv", $application))
            ->assertStatus(404);
    }

    public function testDownloadCvReturns404IfFileDoesNotExistInStorage(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "cv_path" => "applications/cvs/non_existent.pdf",
        ]);

        $this->actingAs($user)
            ->get(route("company.applications.cv", $application))
            ->assertStatus(404);
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
        ]);
    }
}
