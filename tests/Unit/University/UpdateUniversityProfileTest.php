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
            "domain" => "uj.edu.pl",
            "external_form_url" => null,
        ]);

        $data = new UpdateUniversityProfileData(
            domain: "different.edu.pl",
            logo: null,
            externalFormUrl: "https://uj.edu.pl/form",
            faculties: null,
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertEquals("uj.edu.pl", $updated->domain);
        $this->assertEquals("https://uj.edu.pl/form", $updated->external_form_url);
    }

    public function testItUpdatesDomainIfExistingDomainIsEmpty(): void
    {
        $university = University::factory()->approved()->create([
            "domain" => "",
        ]);

        $data = new UpdateUniversityProfileData(
            domain: "newdomain.edu.pl",
            logo: null,
            externalFormUrl: null,
            faculties: null,
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertEquals("newdomain.edu.pl", $updated->domain);
    }

    public function testItUploadsLogoAndDeletesOldOne(): void
    {
        $disk = config("filesystems.default", "local");
        Storage::fake($disk);

        $oldPath = "logos/old-logo.png";
        Storage::disk($disk)->put($oldPath, "fake-data");

        $university = University::factory()->approved()->create([
            "logo_path" => $oldPath,
        ]);

        $newLogo = UploadedFile::fake()->createWithContent("logo.png", $this->fakePng());

        $data = new UpdateUniversityProfileData(
            domain: "uj.edu.pl",
            logo: $newLogo,
            externalFormUrl: null,
            faculties: null,
        );

        $action = new UpdateUniversityProfile(new FileUploadService());
        $updated = $action->execute($university, $data);

        $this->assertNotNull($updated->logo_path);
        $this->assertNotEquals($oldPath, $updated->logo_path);
        Storage::disk($disk)->assertMissing($oldPath);
        Storage::disk($disk)->assertExists($updated->logo_path);
    }

    public function testItSynchronizesFacultiesAndStudyFields(): void
    {
        $university = University::factory()->approved()->create();

        $oldFaculty = $university->faculties()->create(["name" => "Old Faculty"]);
        $oldFaculty->studyFields()->create(["name" => "Old Study Field"]);

        $data = new UpdateUniversityProfileData(
            domain: $university->domain,
            logo: null,
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

    private function fakePng(): string
    {
        return base64_decode(
            "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==",
            true,
        );
    }
}
