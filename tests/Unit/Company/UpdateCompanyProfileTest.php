<?php

declare(strict_types=1);

namespace Tests\Unit\Company;

use App\Actions\Company\UpdateCompanyProfile;
use App\DTO\Company\UpdateCompanyProfileData;
use App\Models\Company;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateCompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesDescriptionAndTagsWithoutLogo(): void
    {
        $company = Company::factory()->approved()->create([
            "description" => null,
            "tags" => null,
        ]);

        $data = new UpdateCompanyProfileData(
            logo: null,
            description: "We build great software.",
            tags: ["PHP", "Laravel"],
            website: null,
            phone: "123456789",
            street: "Testowa",
            postal_code: "00-000",
            city: "Warszawa",
            nip: "1234567890",
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertEquals("We build great software.", $updated->description);
        $this->assertEquals(["PHP", "Laravel"], $updated->tags);
        $this->assertNull($updated->logo_path);
    }

    public function testItUploadsLogoAndStoresPath(): void
    {
        Storage::fake("public");

        $company = Company::factory()->approved()->create(["logo_path" => null]);
        $file = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $data = new UpdateCompanyProfileData(
            logo: $file,
            description: null,
            tags: null,
            website: null,
            phone: "123456789",
            street: "Testowa",
            postal_code: "00-000",
            city: "Warszawa",
            nip: "1234567890",
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertNotNull($updated->logo_path);

        $relativePath = str_replace("/storage/", "", $updated->logo_path);
        Storage::disk("public")->assertExists($relativePath);
    }

    public function testItDeletesOldLogoWhenNewOneIsUploaded(): void
    {
        $disk = "public";
        Storage::fake($disk);

        $oldPath = "logos/old-logo.png";
        Storage::disk($disk)->put($oldPath, "fake-image-data");

        $company = Company::factory()->approved()->create(["logo_path" => "/storage/" . $oldPath]);

        $newFile = UploadedFile::fake()->createWithContent("new-logo.png", $this->fakePng());

        $data = new UpdateCompanyProfileData(
            logo: $newFile,
            description: null,
            tags: null,
            website: null,
            phone: "123456789",
            street: "Testowa",
            postal_code: "00-000",
            city: "Warszawa",
            nip: "1234567890",
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        Storage::disk($disk)->assertMissing($oldPath);

        $newRelativePath = str_replace("/storage/", "", $updated->logo_path);
        Storage::disk($disk)->assertExists($newRelativePath);
        $this->assertNotEquals("/storage/" . $oldPath, $updated->logo_path);
    }

    public function testItKeepsExistingLogoWhenNoNewLogoProvided(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $existingPath = "logos/existing-logo.png";
        Storage::disk($disk)->put($existingPath, "fake-image-data");

        $company = Company::factory()->approved()->create(["logo_path" => "/storage/" . $existingPath]);

        $data = new UpdateCompanyProfileData(
            logo: null,
            description: "Updated description",
            tags: null,
            website: null,
            phone: "123456789",
            street: "Testowa",
            postal_code: "00-000",
            city: "Warszawa",
            nip: "1234567890",
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertEquals("/storage/" . $existingPath, $updated->logo_path);
        Storage::disk($disk)->assertExists($existingPath);
    }

    private function fakePng(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
