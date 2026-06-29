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
use Tests\TestCase;

class UpdateUniversityProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifiedUniversityAdminCanUpdateProfile(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $university = University::factory()->approved()->create([
            "domain" => "uj.edu.pl",
        ]);
        $user = $this->makeUniversityAdmin($university);

        $logo = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => "uj.edu.pl",
                "logo" => $logo,
                "external_form_url" => "https://uj.edu.pl/external-form",
                "faculties" => [
                    [
                        "name" => "Faculty of IT",
                        "study_fields" => ["Computer Science"],
                    ],
                ],
            ])
            ->assertRedirect("/university/profile");

        $university->refresh();
        $this->assertEquals("uj.edu.pl", $university->domain);
        $this->assertEquals("https://uj.edu.pl/external-form", $university->external_form_url);
        $this->assertNotNull($university->logo_path);
        Storage::disk($disk)->assertExists($university->logo_path);

        $this->assertCount(1, $university->faculties);
        $faculty = $university->faculties->first();
        $this->assertEquals("Faculty of IT", $faculty->name);
        $this->assertCount(1, $faculty->studyFields);
        $this->assertEquals("Computer Science", $faculty->studyFields->first()->name);
    }

    public function testAttemptingToChangeDomainWhenAlreadySetReturnsValidationError(): void
    {
        $university = University::factory()->approved()->create([
            "domain" => "uj.edu.pl",
        ]);
        $user = $this->makeUniversityAdmin($university);

        $this->actingAs($user)
            ->patch("/university/profile", [
                "domain" => "another.edu.pl",
                "external_form_url" => null,
                "faculties" => [],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("domain");

        $university->refresh();
        $this->assertEquals("uj.edu.pl", $university->domain);
    }

    public function testUnauthenticatedUserCannotUpdateProfile(): void
    {
        $this->patch("/university/profile", [
            "domain" => "uj.edu.pl",
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
            ])
            ->assertForbidden();
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
