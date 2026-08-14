<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\BuildFacultiesData;
use App\Actions\University\BuildUniversityPublicProfileData;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildUniversityPublicProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsPublicUniversityFields(): void
    {
        $university = University::factory()->approved()->create([
            "name" => "Northfield University",
            "description" => "A university focused on engineering.",
            "email" => "contact@northfield.test",
            "phone" => "123456789",
            "website" => "https://northfield.test",
            "street" => "Elm Street 4",
            "postal_code" => "00-002",
            "city" => "Northfield",
            "logo_path" => "/storage/logos/northfield.png",
            "external_form_url" => "https://northfield.test/form",
        ]);

        $result = (new BuildUniversityPublicProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertSame($university->id, $result["id"]);
        $this->assertSame("Northfield University", $result["name"]);
        $this->assertSame("A university focused on engineering.", $result["description"]);
        $this->assertSame("contact@northfield.test", $result["email"]);
        $this->assertSame("123456789", $result["phone"]);
        $this->assertSame("https://northfield.test", $result["website"]);
        $this->assertSame("Elm Street 4", $result["street"]);
        $this->assertSame("00-002", $result["postalCode"]);
        $this->assertSame("Northfield", $result["city"]);
        $this->assertSame("/storage/logos/northfield.png", $result["logoUrl"]);
        $this->assertSame("https://northfield.test/form", $result["externalFormUrl"]);
    }

    public function testItDoesNotExposeTheAffiliationDomain(): void
    {
        $university = University::factory()->approved()->create(["domain" => "northfield.test"]);

        $result = (new BuildUniversityPublicProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertArrayNotHasKey("domain", $result);
    }

    public function testItReturnsFacultiesAndStudyFieldsSortedByName(): void
    {
        $university = University::factory()->approved()->create();
        $physics = $university->faculties()->create(["name" => "Faculty of Physics"]);
        $computerScience = $university->faculties()->create(["name" => "Faculty of Computer Science"]);
        $computerScience->studyFields()->createMany([
            ["name" => "Software Engineering"],
            ["name" => "Data Science"],
        ]);

        $result = (new BuildUniversityPublicProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertSame(
            ["Faculty of Computer Science", "Faculty of Physics"],
            array_column($result["faculties"], "name"),
        );
        $this->assertSame(
            ["Data Science", "Software Engineering"],
            array_column($result["faculties"][0]["study_fields"], "name"),
        );
        $this->assertSame($physics->id, $result["faculties"][1]["id"]);
        $this->assertSame([], $result["faculties"][1]["study_fields"]);
    }

    public function testItExcludesOtherUniversitiesFaculties(): void
    {
        $university = University::factory()->approved()->create();
        $otherUniversity = University::factory()->approved()->create();
        $otherUniversity->faculties()->create(["name" => "Faculty of Physics"]);

        $result = (new BuildUniversityPublicProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertSame([], $result["faculties"]);
    }
}
