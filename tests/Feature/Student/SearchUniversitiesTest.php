<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchUniversitiesTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotSearchUniversities(): void
    {
        $this->getJson(route("student.universities.search", ["query" => "War"]))
            ->assertStatus(401);
    }

    public function testStudentCanSearchVerifiedUniversitiesByName(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $matching = University::factory()->create([
            "name" => "Warsaw University of Technology",
            "verification_status" => VerificationStatus::Verified,
        ]);
        University::factory()->create([
            "name" => "Warsaw Pending University",
            "verification_status" => VerificationStatus::Pending,
        ]);
        University::factory()->create([
            "name" => "Krakow University",
            "verification_status" => VerificationStatus::Verified,
        ]);

        $response = $this->actingAs($student)
            ->getJson(route("student.universities.search", ["query" => "War"]))
            ->assertOk();

        $response->assertJson([
            "universities" => [
                ["id" => $matching->id, "name" => $matching->name],
            ],
        ]);
    }
}
