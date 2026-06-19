<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Actions\Auth\CreateStudentAccount;
use App\DTO\Auth\StudentRegistrationData;
use App\Enums\UserRole;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateStudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesStudentWithCorrectFieldsAndRole(): void
    {
        $action = new CreateStudentAccount();
        $data = new StudentRegistrationData(
            firstName: "John",
            lastName: "Doe",
            email: "user@example.com",
            password: "Password123!",
            university: "Warsaw University",
        );

        $user = $action->execute($data);

        $this->assertEquals("John", $user->first_name);
        $this->assertEquals("Doe", $user->last_name);
        $this->assertEquals("user@example.com", $user->email);
        $this->assertEquals("Warsaw University", $user->university);
        $this->assertEquals(UserRole::Student, $user->role);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNull($user->organization_id);
    }

    public function testItLinksStudentToUniversityIfDomainMatches(): void
    {
        Notification::fake();

        $university = University::factory()->create([
            "domain" => "example.com",
        ]);

        $action = new CreateStudentAccount();
        $data = new StudentRegistrationData(
            firstName: "John",
            lastName: "Doe",
            email: "user@example.com",
            password: "Password123!",
            university: "Warsaw University",
        );

        $user = $action->execute($data);

        $this->assertEquals($university->id, $user->organization_id);
        $this->assertEquals($university->id, $user->universityOrganization->id);
    }

    public function testItLinksStudentToUniversityIfSubdomainMatchesParentDomain(): void
    {
        Notification::fake();

        $university = University::factory()->create([
            "domain" => "example.com",
        ]);

        $action = new CreateStudentAccount();
        $data = new StudentRegistrationData(
            firstName: "John",
            lastName: "Doe",
            email: "student@sub.example.com",
            password: "Password123!",
            university: "Example University",
        );

        $user = $action->execute($data);

        $this->assertEquals($university->id, $user->organization_id);
        $this->assertEquals($university->id, $user->universityOrganization->id);
    }
}
