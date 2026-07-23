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

class LinkUniversityTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotLinkUniversity(): void
    {
        $university = University::factory()->create(["verification_status" => VerificationStatus::Verified]);

        $response = $this->patch(route("student.university.update"), ["university" => $university->id]);

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotLinkUniversity(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->create(["verification_status" => VerificationStatus::Verified]);

        $response = $this->actingAs($user)->patch(route("student.university.update"), ["university" => $university->id]);

        $response->assertStatus(403);
    }

    public function testStudentCanLinkToVerifiedUniversity(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "organization_id" => null,
        ]);
        $university = University::factory()->create([
            "name" => "Politechnika Wroclawska",
            "verification_status" => VerificationStatus::Verified,
        ]);

        $response = $this->actingAs($student)->patch(route("student.university.update"), [
            "university" => $university->id,
        ]);

        $response->assertRedirect(route("student.profile"));
        $student->refresh();
        $this->assertSame($university->id, $student->organization_id);
        $this->assertSame("Politechnika Wroclawska", $student->university);
    }

    public function testStudentCanSwitchFromOneUniversityToAnother(): void
    {
        $oldUniversity = University::factory()->create(["verification_status" => VerificationStatus::Verified]);
        $newUniversity = University::factory()->create([
            "name" => "New University",
            "verification_status" => VerificationStatus::Verified,
        ]);
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "organization_id" => $oldUniversity->id,
            "university" => $oldUniversity->name,
        ]);

        $this->actingAs($student)->patch(route("student.university.update"), [
            "university" => $newUniversity->id,
        ]);

        $student->refresh();
        $this->assertSame($newUniversity->id, $student->organization_id);
        $this->assertSame("New University", $student->university);
        $this->assertSame(0, $oldUniversity->users()->count());
        $this->assertSame(1, $newUniversity->users()->count());
    }

    public function testCannotLinkToUnverifiedUniversity(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);
        $university = University::factory()->create(["verification_status" => VerificationStatus::Pending]);

        $response = $this->actingAs($student)->patch(route("student.university.update"), [
            "university" => $university->id,
        ]);

        $response->assertSessionHasErrors("university");
    }

    public function testCannotLinkToNonExistentUniversity(): void
    {
        $student = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
        ]);

        $response = $this->actingAs($student)->patch(route("student.university.update"), [
            "university" => "00000000-0000-0000-0000-000000000000",
        ]);

        $response->assertSessionHasErrors("university");
    }
}
