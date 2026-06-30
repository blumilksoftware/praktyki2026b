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
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertEquals("We build great software.", $updated->description);
        $this->assertEquals(["PHP", "Laravel"], $updated->tags);
        $this->assertNull($updated->logo_path);
    }

    public function testItUploadsLogoAndStoresPath(): void
    {
        Storage::fake(config("filesystems.default", "local"));

        $company = Company::factory()->approved()->create(["logo_path" => null]);

        $file = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $data = new UpdateCompanyProfileData(
            logo: $file,
            description: null,
            tags: null,
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertNotNull($updated->logo_path);
        Storage::disk(config("filesystems.default", "local"))->assertExists($updated->logo_path);
    }

    public function testItDeletesOldLogoWhenNewOneIsUploaded(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $oldPath = "logos/old-logo.png";
        Storage::disk($disk)->put($oldPath, "fake-image-data");

        $company = Company::factory()->approved()->create(["logo_path" => $oldPath]);

        $newFile = UploadedFile::fake()->createWithContent("new-logo.png", $this->fakePng());

        $data = new UpdateCompanyProfileData(
            logo: $newFile,
            description: null,
            tags: null,
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        Storage::disk($disk)->assertMissing($oldPath);
        Storage::disk($disk)->assertExists($updated->logo_path);
        $this->assertNotEquals($oldPath, $updated->logo_path);
    }

    public function testItKeepsExistingLogoWhenNoNewLogoProvided(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $existingPath = "logos/existing-logo.png";
        Storage::disk($disk)->put($existingPath, "fake-image-data");

        $company = Company::factory()->approved()->create(["logo_path" => $existingPath]);

        $data = new UpdateCompanyProfileData(
            logo: null,
            description: "Updated description",
            tags: null,
        );

        $action = new UpdateCompanyProfile(new FileUploadService());
        $updated = $action->execute($company, $data);

        $this->assertEquals($existingPath, $updated->logo_path);
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
