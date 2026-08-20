<?php

declare(strict_types=1);

namespace Tests\Unit\University;

use App\Actions\University\UpdateUniversityProfile;
use App\DTO\University\UpdateUniversityProfileData;
use App\Models\University;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateUniversityProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesExternalFormUrlAndPreservesDomainIfAlreadySet(): void
    {
        $university = University::factory()->approved()->create([
            "domain" => "example.com",
            "external_form_url" => null,
        ]);

        $data = new UpdateUniversityProfileData(
            domain: "different.edu.pl",
            logo: null,
            description: null,
            externalFormUrl: "https://example.com/form",
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertEquals("example.com", $updated->domain);
        $this->assertEquals("https://example.com/form", $updated->external_form_url);
    }

    public function testItUpdatesDomainIfExistingDomainIsEmpty(): void
    {
        $university = University::factory()->approved()->create([
            "domain" => "",
        ]);

        $data = new UpdateUniversityProfileData(
            domain: "newdomain.edu.pl",
            logo: null,
            description: null,
            externalFormUrl: null,
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertEquals("newdomain.edu.pl", $updated->domain);
    }

    public function testItUpdatesDescription(): void
    {
        $university = University::factory()->approved()->create([
            "description" => "Outdated description.",
        ]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
            description: "We teach engineering and design.",
            externalFormUrl: null,
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertEquals("We teach engineering and design.", $updated->description);
    }

    public function testItClearsDescriptionWhenNoneIsGiven(): void
    {
        $university = University::factory()->approved()->create([
            "description" => "Description to be removed.",
        ]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
            description: null,
            externalFormUrl: null,
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertNull($updated->description);
    }

    public function testItUploadsLogoAndDeletesOldOne(): void
    {
        Storage::fake("public");

        $oldDiskPath = "logos/old-logo.png";
        Storage::disk("public")->put($oldDiskPath, "fake-data");

        $university = University::factory()->approved()->create([
            "logo_path" => "/storage/" . $oldDiskPath,
        ]);

        $newLogo = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $data = new UpdateUniversityProfileData(
            domain: "example.com",
            logo: $newLogo,
            description: null,
            externalFormUrl: null,
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertNotNull($updated->logo_path);
        $this->assertNotEquals("/storage/" . $oldDiskPath, $updated->logo_path);
        Storage::disk("public")->assertMissing($oldDiskPath);
        $newDiskPath = str_replace("/storage/", "", $updated->logo_path);
        Storage::disk("public")->assertExists($newDiskPath);
    }

    public function testItLeavesFacultiesUntouched(): void
    {
        $university = University::factory()->approved()->create();

        $faculty = $university->faculties()->create(["name" => "Faculty of Engineering"]);
        $studyField = $faculty->studyFields()->create(["name" => "Robotics"]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
            description: null,
            externalFormUrl: null,
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertCount(1, $updated->faculties);
        $this->assertDatabaseHas("faculties", ["id" => $faculty->id, "name" => "Faculty of Engineering"]);
        $this->assertDatabaseHas("study_fields", ["id" => $studyField->id, "name" => "Robotics"]);
    }

    private function fakePng(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
