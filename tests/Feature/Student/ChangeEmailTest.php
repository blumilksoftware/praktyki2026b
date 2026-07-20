<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\EmailChangeVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ChangeEmailTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotChangeEmail(): void
    {
        $response = $this->patch(route("student.email.update"), $this->validPayload());

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotChangeEmail(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotChangeEmail(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload());

        $response->assertStatus(403);
    }

    public function testStudentCanRequestEmailChangeWithCorrectPassword(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload("new@example.com", "old-password"));

        $response->assertRedirect();
        $this->assertEquals("new@example.com", $user->fresh()->pending_email);
        Mail::assertQueued(
            EmailChangeVerificationMail::class,
            fn(EmailChangeVerificationMail $mail): bool => $mail->hasTo("new@example.com") && $mail->newEmail === "new@example.com",
        );
    }

    public function testChangeFailsWithWrongPassword(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload("new@example.com", "wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertNull($user->fresh()->pending_email);
        Mail::assertNothingQueued();
    }

    public function testChangeFailsWhenNewEmailSameAsCurrent(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "email" => "student@example.com",
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload("student@example.com", "old-password"));

        $response->assertInvalid("email");
    }

    public function testChangeFailsWhenEmailAlreadyTakenByAnotherUser(): void
    {
        User::factory()->create(["email" => "taken@example.com"]);
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload("taken@example.com", "old-password"));

        $response->assertInvalid("email");
    }

    public function testChangeFailsWhenEmailAlreadyPendingForAnotherUser(): void
    {
        User::factory()->create(["pending_email" => "pending@example.com"]);
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), $this->validPayload("pending@example.com", "old-password"));

        $response->assertInvalid("email");
    }

    public function testChangeFailsWhenRequiredFieldsAreMissing(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->patch(route("student.email.update"), []);

        $response->assertInvalid(["email", "current_password"]);
    }

    private function validPayload(string $email = "new@example.com", string $currentPassword = "old-password"): array
    {
        return [
            "email" => $email,
            "current_password" => $currentPassword,
        ];
    }
}
