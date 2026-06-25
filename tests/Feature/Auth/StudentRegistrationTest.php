<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\StudentRegistrationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testStudentCanRegisterWithValidData(): void
    {
        Mail::fake();
        Notification::fake();

        $response = $this->post("/register/student", $this->validPayload());

        $response->assertRedirect(route("login"));
        $this->assertDatabaseHas("users", ["email" => "user@example.com"]);
    }

    public function testRegistrationFailsWithDuplicateEmail(): void
    {
        User::factory()->create(["email" => "user@example.com"]);

        $response = $this->post("/register/student", $this->validPayload());

        $response->assertInvalid("email");
    }

    public function testRegistrationRequiresTermsAcceptance(): void
    {
        $response = $this->post("/register/student", $this->validPayload(["terms" => false]));

        $response->assertInvalid("terms");
    }

    public function testRegistrationRequiresPasswordConfirmation(): void
    {
        $response = $this->post("/register/student", $this->validPayload(["password_confirmation" => "different"]));

        $response->assertInvalid("password");
    }

    public function testRegistrationSendsConfirmationEmail(): void
    {
        Mail::fake();
        Notification::fake();

        $this->post("/register/student", $this->validPayload());

        Mail::assertQueued(StudentRegistrationMail::class, fn($mail): bool => $mail->hasTo("user@example.com"));
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            "first_name" => "Jan",
            "last_name" => "Kowalski",
            "email" => "user@example.com",
            "password" => "Password123!",
            "password_confirmation" => "Password123!",
            "terms" => true,
        ], $overrides);
    }
}
