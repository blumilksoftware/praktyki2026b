<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UpdateUniversityProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifiedUniversityAdminCanUpdateProfile(): void
    {
        Storage::fake("public");

        $university = University::factory()->approved()->create([
            "domain" => "example.com",
        ]);
        $user = $this->makeUniversityAdmin($university);

        $logo = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => "example.com",
                "logo" => $logo,
                "description" => "A technical university focused on engineering.",
                "external_form_url" => "https://example.com/external-form",
                "faculties" => [
                    [
                        "name" => "Faculty of IT",
                        "study_fields" => ["Computer Science"],
                    ],
                ],
                "website" => "https://example.com",
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
            ])
            ->assertRedirect("/university/profile");

        $university->refresh();
        $this->assertEquals("example.com", $university->domain);
        $this->assertEquals("A technical university focused on engineering.", $university->description);
        $this->assertEquals("https://example.com/external-form", $university->external_form_url);
        $this->assertNotNull($university->logo_path);
        $expectedDiskPath = str_replace("/storage/", "", $university->logo_path);
        Storage::disk("public")->assertExists($expectedDiskPath);

        $this->assertEquals("https://example.com", $university->website);
        $this->assertEquals("+48 123 456 789", $university->phone);
        $this->assertEquals("Main Street 1", $university->street);
        $this->assertEquals("30-001", $university->postal_code);
        $this->assertEquals("Kraków", $university->city);

        $this->assertCount(1, $university->faculties);
        $faculty = $university->faculties->first();
        $this->assertEquals("Faculty of IT", $faculty->name);
        $this->assertCount(1, $faculty->studyFields);
        $this->assertEquals("Computer Science", $faculty->studyFields->first()->name);
    }

    public function testAttemptingToChangeDomainWhenAlreadySetReturnsValidationError(): void
    {
        $university = University::factory()->approved()->create([
            "domain" => "example.com",
        ]);
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => "another.edu.pl",
                "external_form_url" => null,
                "faculties" => [],
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("domain");

        $university->refresh();
        $this->assertEquals("example.com", $university->domain);
    }

    public function testDescriptionIsPassedToProfileAndEditPages(): void
    {
        $university = University::factory()->approved()->create([
            "description" => "A technical university focused on engineering.",
        ]);
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->get("/university/profile")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/Profile/Show")
                    ->where("university.description", "A technical university focused on engineering."),
            );

        $this->actingAs($user)
            ->get("/university/profile/edit")
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/Profile/Edit")
                    ->where("university.description", "A technical university focused on engineering."),
            );
    }

    public function testUnauthenticatedUserCannotUpdateProfile(): void
    {
        $this->patch("/university/profile", [
            "domain" => "example.com",
        ])->assertRedirect("/login");
    }

    public function testPendingUniversityAdminCannotUpdateProfile(): void
    {
        $university = University::factory()->pending()->create();
        $user = User::factory()->pendingUniversityAdmin()->create([
            "organization_id" => $university->id,
        ]);

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => $university->domain,
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
            ])
            ->assertRedirect("/university/verification/pending");
    }

    public function testLogoValidationFailsForInvalidMimeType(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $invalidFile = UploadedFile::fake()->create("document.pdf", 100, "application/pdf");

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => $university->domain,
                "logo" => $invalidFile,
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("logo");
    }

    public function testLogoValidationFailsWhenFileTooLarge(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $oversizedContent = $this->fakePng() . str_repeat("X", 2 * 1024 * 1024 + 1);
        $oversizedFile = UploadedFile::fake()->createWithContent("large-logo.png", $oversizedContent);

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => $university->domain,
                "logo" => $oversizedFile,
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("logo");
    }

    public function testValidationFailsForInvalidFacultiesNesting(): void
    {
        $university = University::factory()->approved()->create();
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => $university->domain,
                "phone" => "+48 123 456 789",
                "street" => "Main Street 1",
                "postalCode" => "30-001",
                "city" => "Kraków",
                "faculties" => [
                    [
                        "name" => "",
                        "study_fields" => "not-an-array",
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors([
                "faculties.0.name",
                "faculties.0.study_fields",
            ]);
    }

    private function makeUniversityAdmin(University $university): User
    {
        return User::factory()->create([
            "role" => UserRole::UniversityAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $university->id,
            "first_name" => null,
            "last_name" => null,
        ]);
    }

    private function fakePng(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
