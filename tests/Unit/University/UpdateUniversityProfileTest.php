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
            faculties: null,
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
            faculties: null,
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
            faculties: null,
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
            faculties: null,
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
            faculties: null,
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

    public function testItSynchronizesFacultiesAndStudyFields(): void
    {
        $university = University::factory()->approved()->create();

        $oldFaculty = $university->faculties()->create(["name" => "Old Faculty"]);
        $oldFaculty->studyFields()->create(["name" => "Old Study Field"]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
            description: null,
            externalFormUrl: null,
            faculties: [
                [
                    "name" => "Faculty of Physics",
                    "study_fields" => ["Astronomy", "Theoretical Physics"],
                ],
                [
                    "name" => "Faculty of Mathematics",
                    "study_fields" => ["Pure Mathematics"],
                ],
            ],
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertCount(2, $updated->faculties);
        $this->assertDatabaseMissing("faculties", ["name" => "Old Faculty"]);
        $this->assertDatabaseMissing("study_fields", ["name" => "Old Study Field"]);

        $this->assertDatabaseHas("faculties", [
            "university_id" => $university->id,
            "name" => "Faculty of Physics",
        ]);
        $this->assertDatabaseHas("faculties", [
            "university_id" => $university->id,
            "name" => "Faculty of Mathematics",
        ]);

        $physicsFaculty = $updated->faculties()->where("name", "Faculty of Physics")->first();
        $mathFaculty = $updated->faculties()->where("name", "Faculty of Mathematics")->first();

        $this->assertDatabaseHas("study_fields", [
            "faculty_id" => $physicsFaculty->id,
            "name" => "Astronomy",
        ]);
        $this->assertDatabaseHas("study_fields", [
            "faculty_id" => $physicsFaculty->id,
            "name" => "Theoretical Physics",
        ]);
        $this->assertDatabaseHas("study_fields", [
            "faculty_id" => $mathFaculty->id,
            "name" => "Pure Mathematics",
        ]);
    }

    public function testItDoesNotDeleteFacultiesWhenFacultiesArrayIsEmpty(): void
    {
        $university = University::factory()->approved()->create();

        $oldFaculty = $university->faculties()->create(["name" => "Old Faculty"]);
        $oldFaculty->studyFields()->create(["name" => "Old Study Field"]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
            description: null,
            externalFormUrl: null,
            faculties: [],
            website: "https://example.com",
            phone: "123456789",
            street: "Test Street 1",
            postalCode: "00-000",
            city: "Test City",
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertCount(1, $updated->faculties);
        $this->assertDatabaseHas("faculties", ["name" => "Old Faculty"]);
        $this->assertDatabaseHas("study_fields", ["name" => "Old Study Field"]);
    }

    private function fakePng(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
