<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateCompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifiedCompanyAdminCanUpdateProfile(): void
    {
        Storage::fake(config("filesystems.default", "local"));

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $this->actingAs($user)
            ->patch("/company/profile", [
                "description" => "We build great software.",
                "tags" => ["PHP", "Laravel", "Vue"],
            ])
            ->assertRedirect("/company/profile");

        $this->assertDatabaseHas("companies", [
            "id" => $company->id,
            "description" => "We build great software.",
        ]);
    }

    public function testVerifiedCompanyAdminCanUploadLogo(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $company = Company::factory()->approved()->create(["logo_path" => null]);
        $user = $this->makeCompanyAdmin($company);

        $logo = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $this->actingAs($user)
            ->patch("/company/profile", [
                "logo" => $logo,
            ])
            ->assertRedirect("/company/profile");

        $company->refresh();
        $this->assertNotNull($company->logo_path);
        Storage::disk($disk)->assertExists($company->logo_path);
    }

    public function testUnauthenticatedUserCannotUpdateProfile(): void
    {
        $this->patch("/company/profile", [
            "description" => "Some description",
        ])->assertRedirect("/login");
    }

    public function testPendingCompanyAdminCannotUpdateProfile(): void
    {
        $company = Company::factory()->pending()->create();
        $user = User::factory()->pendingCompanyAdmin()->create([
            "organization_id" => $company->id,
        ]);

        $this->actingAs($user)
            ->patch("/company/profile", [
                "description" => "Some description",
            ])
            ->assertForbidden();
    }

    public function testLogoValidationFailsForInvalidMimeType(): void
    {
        Storage::fake(config("filesystems.default", "local"));

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $fakeFile = UploadedFile::fake()->create("malicious.pdf", 100, "application/pdf");

        $this->actingAs($user)
            ->patch("/company/profile", [
                "logo" => $fakeFile,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("logo");
    }

    public function testLogoValidationFailsWhenFileTooLarge(): void
    {
        Storage::fake(config("filesystems.default", "local"));

        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $oversizedContent = $this->fakePng() . str_repeat("X", 2 * 1024 * 1024 + 1);
        $oversizedFile = UploadedFile::fake()->createWithContent("large-logo.png", $oversizedContent);

        $this->actingAs($user)
            ->patch("/company/profile", [
                "logo" => $oversizedFile,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("logo");
    }

    public function testTagsValidationFailsWhenTooManyTags(): void
    {
        $company = Company::factory()->approved()->create();
        $user = $this->makeCompanyAdmin($company);

        $tooManyTags = array_fill(0, 21, "tag");

        $this->actingAs($user)
            ->patch("/company/profile", [
                "tags" => $tooManyTags,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors("tags");
    }

    public function testUpdateWithNullValuesIsAllowed(): void
    {
        $company = Company::factory()->approved()->create([
            "description" => "Old description",
            "tags" => ["old-tag"],
        ]);
        $user = $this->makeCompanyAdmin($company);

        $this->actingAs($user)
            ->patch("/company/profile", [
                "description" => null,
                "tags" => null,
            ])
            ->assertRedirect("/company/profile");
    }

    private function makeCompanyAdmin(Company $company): User
    {
        return User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "organization_id" => $company->id,
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
