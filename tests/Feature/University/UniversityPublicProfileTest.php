<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UniversityPublicProfileTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanViewApprovedUniversityProfile(): void
    {
        $university = University::factory()->approved()->create([
            "name" => "Northfield University",
            "description" => "A university focused on engineering.",
            "website" => "https://northfield.test",
            "external_form_url" => "https://northfield.test/form",
        ]);
        $faculty = $university->faculties()->create(["name" => "Faculty of Computer Science"]);
        $faculty->studyFields()->create(["name" => "Software Engineering"]);

        $this->get(route("universities.show", $university))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/PublicProfile")
                    ->where("university.id", $university->id)
                    ->where("university.name", "Northfield University")
                    ->where("university.description", "A university focused on engineering.")
                    ->where("university.website", "https://northfield.test")
                    ->where("university.externalFormUrl", "https://northfield.test/form")
                    ->has("university.faculties", 1)
                    ->where("university.faculties.0.name", "Faculty of Computer Science")
                    ->where("university.faculties.0.study_fields.0.name", "Software Engineering"),
            );
    }

    public function testProfileDoesNotExposeTheAffiliationDomain(): void
    {
        $university = University::factory()->approved()->create(["domain" => "northfield.test"]);

        $this->get(route("universities.show", $university))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->missing("university.domain"));
    }

    public function testPendingUniversityProfileReturns404(): void
    {
        $university = University::factory()->pending()->create();

        $this->get(route("universities.show", $university))->assertNotFound();
    }

    public function testRejectedUniversityProfileReturns404(): void
    {
        $university = University::factory()->rejected()->create();

        $this->get(route("universities.show", $university))->assertNotFound();
    }

    public function testUnknownUniversityReturns404(): void
    {
        $this->get(route("universities.show", "3f1a7c64-9d2e-4b8a-8c5f-2a1b0d3e4f56"))->assertNotFound();
    }

    public function testAdminCanViewPendingUniversityProfile(): void
    {
        $admin = User::factory()->create(["role" => UserRole::SuperAdmin]);
        $university = University::factory()->pending()->create();

        $this->actingAs($admin)
            ->get(route("universities.show", $university))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/PublicProfile")
                    ->where("university.id", $university->id),
            );
    }
}
