<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\University;

use App\Actions\University\BuildFacultiesData;
use App\Actions\University\BuildUniversityProfileData;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildUniversityProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsUniversityProfileFields(): void
    {
        $university = University::factory()->approved()->create([
            "name" => "Northfield University",
            "description" => "A university focused on engineering.",
            "email" => "contact@northfield.test",
            "domain" => "northfield.test",
            "phone" => "123456789",
            "website" => "https://northfield.test",
            "street" => "Elm Street 4",
            "postal_code" => "00-002",
            "city" => "Northfield",
            "logo_path" => "/storage/logos/northfield.png",
            "external_form_url" => "https://northfield.test/form",
        ]);

        $result = (new BuildUniversityProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertSame($university->id, $result["id"]);
        $this->assertSame("Northfield University", $result["name"]);
        $this->assertSame("A university focused on engineering.", $result["description"]);
        $this->assertSame("contact@northfield.test", $result["email"]);
        $this->assertSame("northfield.test", $result["domain"]);
        $this->assertSame("123456789", $result["phone"]);
        $this->assertSame("https://northfield.test", $result["website"]);
        $this->assertSame("Elm Street 4", $result["street"]);
        $this->assertSame("00-002", $result["postalCode"]);
        $this->assertSame("Northfield", $result["city"]);
        $this->assertSame("/storage/logos/northfield.png", $result["logoUrl"]);
        $this->assertSame("https://northfield.test/form", $result["externalFormUrl"]);
    }

    public function testItReturnsFacultiesWithTheirStudyFields(): void
    {
        $university = University::factory()->approved()->create();
        $faculty = $university->faculties()->create(["name" => "Faculty of Computer Science"]);
        $faculty->studyFields()->create(["name" => "Software Engineering"]);

        $result = (new BuildUniversityProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertCount(1, $result["faculties"]);
        $this->assertSame("Faculty of Computer Science", $result["faculties"][0]["name"]);
        $this->assertSame("Software Engineering", $result["faculties"][0]["study_fields"][0]["name"]);
    }

    public function testItExcludesOtherUniversitiesFaculties(): void
    {
        $university = University::factory()->approved()->create();
        $otherUniversity = University::factory()->approved()->create();
        $otherUniversity->faculties()->create(["name" => "Faculty of Physics"]);

        $result = (new BuildUniversityProfileData(new BuildFacultiesData()))->execute($university);

        $this->assertSame([], $result["faculties"]);
    }
}
