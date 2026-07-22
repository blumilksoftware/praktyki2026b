<?php

declare(strict_types=1);

namespace Tests\Feature\Student;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotChangePassword(): void
    {
        $response = $this->put(route("student.password.update"), $this->validPayload());

        $response->assertRedirect(route("login"));
    }

    public function testNonStudentRoleCannotChangePassword(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::CompanyAdmin,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), $this->validPayload("old-password"));

        $response->assertStatus(403);
    }

    public function testInactiveStudentCannotChangePassword(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Pending,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), $this->validPayload("old-password"));

        $response->assertStatus(403);
    }

    public function testStudentCanChangePasswordWithCorrectCurrentPassword(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), $this->validPayload("old-password"));

        $response->assertRedirect();
        $this->assertTrue(Hash::check("new-password-123", $user->fresh()->password));
    }

    public function testChangeFailsWithWrongCurrentPassword(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), $this->validPayload("wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertTrue(Hash::check("old-password", $user->fresh()->password));
    }

    public function testChangeFailsWhenNewPasswordConfirmationDoesNotMatch(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), [
            "current_password" => "old-password",
            "password" => "new-password-123",
            "password_confirmation" => "different-password",
        ]);

        $response->assertInvalid("password");
        $this->assertTrue(Hash::check("old-password", $user->fresh()->password));
    }

    public function testChangeFailsWhenRequiredFieldsAreMissing(): void
    {
        $user = User::factory()->create([
            "role" => UserRole::Student,
            "status" => UserStatus::Active,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($user)->put(route("student.password.update"), []);

        $response->assertInvalid(["current_password", "password"]);
    }

    private function validPayload(string $currentPassword = "old-password"): array
    {
        return [
            "current_password" => $currentPassword,
            "password" => "new-password-123",
            "password_confirmation" => "new-password-123",
        ];
    }
}
