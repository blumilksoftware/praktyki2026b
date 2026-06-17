<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\StudentRegistrationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testStudentCanRegisterWithValidData(): void
    {
        Mail::fake();

        $response = $this->postJson("/api/register/student", $this->validPayload());

        $response->assertCreated();
        $this->assertDatabaseHas("users", ["email" => "user@example.com"]);
    }

    public function testRegistrationFailsWithDuplicateEmail(): void
    {
        User::factory()->create(["email" => "user@example.com"]);

        $response = $this->postJson("/api/register/student", $this->validPayload());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors("email");
    }

    public function testRegistrationRequiresTermsAcceptance(): void
    {
        $response = $this->postJson("/api/register/student", $this->validPayload(["terms" => false]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors("terms");
    }

    public function testRegistrationRequiresPasswordConfirmation(): void
    {
        $response = $this->postJson("/api/register/student", $this->validPayload(["password_confirmation" => "different"]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors("password");
    }

    public function testRegistrationSendsConfirmationEmail(): void
    {
        Mail::fake();

        $this->postJson("/api/register/student", $this->validPayload());

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
