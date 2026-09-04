<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Models\Application;
use App\Models\Company;
use App\Models\Faculty;
use App\Models\Offer;
use App\Models\StudentPreferredCity;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = config("filesystems.default", "local");
        Storage::fake($this->disk);
    }

    public function testGuestCannotViewStudentProfile(): void
    {
        $student = User::factory()->create();

        $this->get(route("company.students.show", $student))->assertRedirect(route("login"));
    }

    public function testStudentCannotViewAnotherStudentProfile(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();

        $this->actingAs($otherStudent)
            ->get(route("company.students.show", $student))
            ->assertStatus(403);
    }

    public function testUniversityAdminCannotViewStudentProfile(): void
    {
        $university = University::factory()->create();
        $universityAdmin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $student = User::factory()->create();

        $this->actingAs($universityAdmin)
            ->get(route("company.students.show", $student))
            ->assertStatus(403);
    }

    public function testUnverifiedCompanyIsRedirectedToVerificationPending(): void
    {
        $company = Company::factory()->pending()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertRedirect(route("company.verification.pending"));
    }

    public function testCompanyCannotViewProfileOfStudentWhoDidNotApplyToTheirOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $student = $this->makeStudentWithApplicationTo($otherCompany);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertStatus(403);
    }

    public function testCompanyCannotViewProfileOfCompanyUser(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $colleague = User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $this->actingAs($user)
            ->get(route("company.students.show", $colleague))
            ->assertStatus(403);
    }

    public function testCompanyMemberCanViewProfileOfStudentWhoAppliedToTheirOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $member = User::factory()->companyMember()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $this->actingAs($member)
            ->get(route("company.students.show", $student))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component("Student/PublicProfile"));
    }

    public function testCompanyAdminSeesStudentProfileDetails(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $university = University::factory()->create(["name" => "Test University"]);
        $faculty = Faculty::factory()->for($university)->create(["name" => "Faculty of Engineering"]);
        $enrolledField = StudyField::factory()->for($faculty)->create(["name" => "Computer Science"]);

        $student = User::factory()->create([
            "first_name" => "John",
            "last_name" => "Doe",
            "city" => "Student City",
            "organization_id" => $university->id,
            "university" => "Manually Typed University",
            "study_field_id" => $enrolledField->id,
            "study_year" => 3,
            "specialization" => "Distributed Systems",
            "skills" => ["PHP", "Vue"],
            "work_modes" => ["remote", "hybrid"],
        ]);

        StudentPreferredCity::create(["student_id" => $student->id, "city" => "Preferred City", "latitude" => 50.06, "longitude" => 19.94]);
        $student->preferredStudyFields()->attach($enrolledField);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $application = Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "cv_path" => "applications/cvs/john_doe.pdf",
        ]);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Student/PublicProfile")
                    ->where("student.full_name", "John Doe")
                    ->where("student.email", $student->email)
                    ->where("student.city", "Student City")
                    ->where("student.university", "Test University")
                    ->where("student.study_field", "Computer Science")
                    ->where("student.study_year", 3)
                    ->where("student.specialization", "Distributed Systems")
                    ->where("student.skills", ["PHP", "Vue"])
                    ->where("student.work_modes", ["remote", "hybrid"])
                    ->where("student.preferred_cities", ["Preferred City"])
                    ->where("student.preferred_study_fields", ["Computer Science"])
                    ->where("cvUrl", route("company.applications.cv", $application))
                    ->missing("student.pending_email")
                    ->missing("student.email_verified_at")
                    ->missing("student.cv_path")
                    ->missing("student.street")
                    ->missing("student.postal_code")
                    ->etc(),
            );
    }

    public function testUniversityIdIsExposedOnlyForVerifiedUniversities(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $verifiedUniversity = University::factory()->approved()->create();
        $verifiedStudent = $this->makeStudentWithApplicationTo($company);
        $verifiedStudent->update(["organization_id" => $verifiedUniversity->id]);

        $this->actingAs($user)
            ->get(route("company.students.show", $verifiedStudent))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where("student.university_id", $verifiedUniversity->id)->etc());

        $pendingUniversity = University::factory()->pending()->create();
        $pendingStudent = $this->makeStudentWithApplicationTo($company);
        $pendingStudent->update(["organization_id" => $pendingUniversity->id]);

        $this->actingAs($user)
            ->get(route("company.students.show", $pendingStudent))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where("student.university_id", null)->etc());
    }

    public function testCvUrlIsNullWhenStudentAppliedWithoutCv(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where("cvUrl", null)->etc());
    }

    public function testCvUrlPointsToTheLatestApplicationWithCv(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = User::factory()->create();

        $firstOffer = Offer::factory()->create(["company_id" => $company->id]);
        Application::factory()->create([
            "offer_id" => $firstOffer->id,
            "student_id" => $student->id,
            "cv_path" => "applications/cvs/old.pdf",
            "created_at" => now()->subMonth(),
        ]);

        $secondOffer = Offer::factory()->create(["company_id" => $company->id]);
        $latest = Application::factory()->create([
            "offer_id" => $secondOffer->id,
            "student_id" => $student->id,
            "cv_path" => "applications/cvs/new.pdf",
        ]);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where("cvUrl", route("company.applications.cv", $latest))->etc());
    }

    public function testStudentPhotoUrlIsIncludedInPublicProfileWhenPhotoIsSet(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);
        $student->update(["photo_path" => "photos/john_doe.jpg"]);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->where("student.photo_url", route("students.photo", $student))
                    ->etc(),
            );
    }

    public function testStudentPhotoUrlIsNullWhenStudentHasNoPhoto(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertInertia(fn(Assert $page) => $page->where("student.photo_url", null)->etc());
    }

    public function testGuestCannotViewStudentPhoto(): void
    {
        $student = User::factory()->create();

        $this->get(route("company.students.photo", $student))->assertRedirect(route("login"));
    }

    public function testCompanyCannotViewPhotoOfStudentWhoDidNotApplyToTheirOffer(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $otherCompany = Company::factory()->approved()->create();
        $student = $this->makeStudentWithApplicationTo($otherCompany);

        $this->actingAs($user)
            ->get(route("company.students.photo", $student))
            ->assertStatus(403);
    }

    public function testCompanyAdminCanViewPhotoOfStudentWhoApplied(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $photoPath = "photos/john_doe.jpg";
        Storage::disk($this->disk)->put($photoPath, "fake-image-content");
        $student->update(["photo_path" => $photoPath]);

        $this->actingAs($user)
            ->get(route("company.students.photo", $student))
            ->assertOk();
    }

    public function testViewPhotoReturns404WhenStudentHasNoPhoto(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $student = $this->makeStudentWithApplicationTo($company);

        $this->actingAs($user)
            ->get(route("company.students.photo", $student))
            ->assertStatus(404);
    }

    public function testCompanyCanViewProfileWhenTheOfferHasBeenSoftDeleted(): void
    {
        $company = Company::factory()->approved()->create();
        $user = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $student = User::factory()->create();
        Application::factory()->create(["offer_id" => $offer->id, "student_id" => $student->id]);
        $offer->delete();

        $this->actingAs($user)
            ->get(route("company.students.show", $student))
            ->assertOk();
    }

    private function makeStudentWithApplicationTo(Company $company): User
    {
        $offer = Offer::factory()->create(["company_id" => $company->id]);
        $student = User::factory()->create();

        Application::factory()->create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "cv_path" => null,
        ]);

        return $student;
    }
}
