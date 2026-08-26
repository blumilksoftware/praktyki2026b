<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WorkMode;
use App\Models\Faculty;
use App\Models\StudentPreferredCity;
use App\Models\StudyField;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotUpdateProfile(): void
    {
        $response = $this->patch(route("student.profile.update"), $this->validPayload());

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotUpdateProfile(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotUpdateProfile(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testStudentCanUpdateBasicProfileFields(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "first_name" => "John",
            "last_name" => "Doe",
            "university" => "Northgate University",
            "study_year" => 2,
            "specialization" => "Backend",
        ]));

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals("John", $user->first_name);
        $this->assertEquals("Doe", $user->last_name);
        $this->assertEquals("Northgate University", $user->university);
        $this->assertEquals(2, $user->study_year);
        $this->assertEquals("Backend", $user->specialization);
    }

    public function testStudentIsLinkedToTheChosenUniversityAndFieldOfStudy(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->approved()->create();
        $faculty = Faculty::factory()->for($university)->create();
        $studyField = StudyField::factory()->for($faculty)->create();

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "university" => $university->name,
            "university_id" => $university->id,
            "study_field_id" => $studyField->id,
        ]));

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals($university->id, $user->organization_id);
        $this->assertEquals($studyField->id, $user->study_field_id);
    }

    public function testUnverifiedUniversityCannotBeLinked(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->create();

        $this->actingAs($user)
            ->patch(route("student.profile.update"), $this->validPayload([
                "university" => $university->name,
                "university_id" => $university->id,
            ]))
            ->assertInvalid("university_id");
    }

    public function testFieldOfStudyFromAnotherUniversityIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->approved()->create();
        $foreignFaculty = Faculty::factory()->for(University::factory()->approved()->create())->create();
        $foreignField = StudyField::factory()->for($foreignFaculty)->create();

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "university" => $university->name,
            "university_id" => $university->id,
            "study_field_id" => $foreignField->id,
        ]));

        $response->assertInvalid("study_field_id");
    }

    public function testProfileUpdateAcceptsStringNumericStudyYearFromForm(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "study_year" => "3",
        ]));

        $response->assertRedirect(route("student.profile"));
        $user->refresh();

        $this->assertEquals(3, $user->study_year);
    }

    public function testMissingRequiredFieldsAreRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), []);

        $response->assertInvalid(["first_name", "last_name"]);
    }

    public function testStudentCanSyncPreferredStudyFields(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $studyField = StudyField::factory()->create();

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "study_field_ids" => [$studyField->id],
        ]));

        $response->assertRedirect();
        $this->assertTrue($user->preferredStudyFields()->where("study_fields.id", $studyField->id)->exists());
    }

    public function testInvalidStudyFieldIdIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "study_field_ids" => ["11111111-1111-1111-1111-111111111111"],
        ]));

        $response->assertInvalid("study_field_ids.0");
    }

    public function testStudentCanAddPreferredCityAndItIsGeocoded(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        Http::fake([
            "api.mapbox.com/*" => Http::response([
                "features" => [
                    ["center" => [21.0122, 52.2297]],
                ],
            ]),
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "preferred_cities" => ["Warszawa"],
        ]));

        $response->assertRedirect();
        $city = StudentPreferredCity::where("student_id", $user->id)->where("city", "Warszawa")->first();
        $this->assertNotNull($city);
        $this->assertEquals(52.2297, $city->latitude);
        $this->assertEquals(21.0122, $city->longitude);
    }

    public function testRemovingCityFromListDeletesIt(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        StudentPreferredCity::create([
            "student_id" => $user->id,
            "city" => "Krakow",
            "latitude" => 50.0647,
            "longitude" => 19.9450,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "preferred_cities" => [],
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing("student_preferred_cities", [
            "student_id" => $user->id,
            "city" => "Krakow",
        ]);
    }

    public function testMoreThanTenPreferredCitiesIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "preferred_cities" => array_map(fn(int $i): string => "City{$i}", range(1, 11)),
        ]));

        $response->assertInvalid("preferred_cities");
    }

    public function testGeocodingFailureRollsBackWithValidationError(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        Http::fake([
            "api.mapbox.com/*" => Http::response([], 500),
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "preferred_cities" => ["Nieznane Miasto"],
        ]));

        $response->assertInvalid("preferred_cities");
        $this->assertDatabaseMissing("student_preferred_cities", [
            "student_id" => $user->id,
            "city" => "Nieznane Miasto",
        ]);
    }

    public function testStudentCanUpdateAddressFields(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "street" => "Student Street",
            "postal_code" => "12-345",
            "city" => "Student City",
        ]));

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals("Student Street", $user->street);
        $this->assertEquals("12-345", $user->postal_code);
        $this->assertEquals("Student City", $user->city);
    }

    public function testInvalidPostalCodeIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "postal_code" => "invalid",
        ]));

        $response->assertInvalid("postal_code");
    }

    public function testStudentCanSaveSkillsAndWorkModes(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "skills" => ["Vue.js", "Python"],
            "work_modes" => [WorkMode::Remote->value, WorkMode::Hybrid->value],
        ]));

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals(["Vue.js", "Python"], $user->skills);
        $this->assertEquals([WorkMode::Remote->value, WorkMode::Hybrid->value], $user->work_modes);
    }

    public function testMoreThanFifteenSkillsIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "skills" => array_map(fn(int $i): string => "Skill{$i}", range(1, 16)),
        ]));

        $response->assertInvalid("skills");
    }

    public function testDuplicateSkillCaseInsensitiveIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "skills" => ["Python", "python"],
        ]));

        $response->assertInvalid("skills.0");
    }

    public function testInvalidWorkModeValueIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "work_modes" => ["from_home"],
        ]));

        $response->assertInvalid("work_modes.0");
    }

    public function testDuplicateWorkModeIsRejected(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($user)->patch(route("student.profile.update"), $this->validPayload([
            "work_modes" => [WorkMode::Remote->value, WorkMode::Remote->value],
        ]));

        $response->assertInvalid("work_modes.0");
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            "first_name" => "John",
            "last_name" => "Doe",
        ], $overrides);
    }
}
