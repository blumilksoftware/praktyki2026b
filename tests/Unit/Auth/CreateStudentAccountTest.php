<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Actions\Auth\CreateStudentAccount;
use App\DTO\Auth\StudentRegistrationData;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateStudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesStudentWithCorrectFieldsAndRole(): void
    {
        $action = new CreateStudentAccount();
        $data = new StudentRegistrationData(
            firstName: "Jan",
            lastName: "Kowalski",
            email: "user@example.com",
            password: "Password123!",
            university: "Warsaw University",
        );

        $user = $action->execute($data);

        $this->assertEquals("Jan", $user->first_name);
        $this->assertEquals("Kowalski", $user->last_name);
        $this->assertEquals("user@example.com", $user->email);
        $this->assertEquals("Warsaw University", $user->university);
        $this->assertEquals(UserRole::Student, $user->role);
        $this->assertNotNull($user->terms_accepted_at);
    }
}
