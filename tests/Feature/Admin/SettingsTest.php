<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\EmailChangeVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsTest extends TestCase
{
use RefreshDatabase;

public function testGuestCannotAccessSettingsOrMutateAccount(): void
{
$this->get(route("admin.settings"))->assertRedirect(route("login"));
$this->put(route("admin.password.update"), $this->passwordPayload())->assertRedirect(route("login"));
$this->patch(route("admin.email.update"), $this->emailPayload())->assertRedirect(route("login"));
}

public function testNonAdminRoleCannotAccessSettingsOrMutateAccount(): void
{
$student = User::factory()->create(["role" => UserRole::Student, "status" => UserStatus::Active]);

$this->actingAs($student)->get(route("admin.settings"))->assertStatus(403);
$this->actingAs($student)->put(route("admin.password.update"), $this->passwordPayload())->assertStatus(403);
$this->actingAs($student)->patch(route("admin.email.update"), $this->emailPayload())->assertStatus(403);
}

public function testAdminCanSeeSettingsPage(): void
{
$admin = User::factory()->create([
"role" => UserRole::SuperAdmin,
"status" => UserStatus::Active,
"email" => "admin@example.com",
]);

$this->actingAs($admin)
->get(route("admin.settings"))
->assertOk()
->assertInertia(
fn(Assert $page) => $page
->component("Admin/Settings")
->where("email", "admin@example.com"),
);
}

public function testAdminCanChangePasswordWithCorrectCurrentPassword(): void
{
$admin = User::factory()->create([
"role" => UserRole::SuperAdmin,
"status" => UserStatus::Active,
"password" => "old-password",
]);

$response = $this->actingAs($admin)->put(route("admin.password.update"), $this->passwordPayload("old-password"));

$response->assertRedirect();
$this->assertTrue(Hash::check("new-password-123", $admin->fresh()->password));
}

public function testChangePasswordFailsWithWrongCurrentPassword(): void
{
$admin = User::factory()->create([
"role" => UserRole::SuperAdmin,
"status" => UserStatus::Active,
"password" => "old-password",
]);

$response = $this->actingAs($admin)->put(route("admin.password.update"), $this->passwordPayload("wrong-password"));

$response->assertInvalid("current_password");
$this->assertTrue(Hash::check("old-password", $admin->fresh()->password));
}

public function testAdminCanRequestEmailChangeWithCorrectPassword(): void
{
Mail::fake();
$admin = User::factory()->create([
"role" => UserRole::SuperAdmin,
"status" => UserStatus::Active,
"password" => "old-password",
]);

$response = $this->actingAs($admin)->patch(route("admin.email.update"), $this->emailPayload("new@example.com", "old-password"));

$response->assertRedirect();
$this->assertEquals("new@example.com", $admin->fresh()->pending_email);
Mail::assertQueued(
EmailChangeVerificationMail::class,
fn(EmailChangeVerificationMail $mail): bool => $mail->hasTo("new@example.com"),
);
}

public function testChangeEmailFailsWithWrongPassword(): void
{
Mail::fake();
$admin = User::factory()->create([
"role" => UserRole::SuperAdmin,
"status" => UserStatus::Active,
"password" => "old-password",
]);

$response = $this->actingAs($admin)->patch(route("admin.email.update"), $this->emailPayload("new@example.com", "wrong-password"));

$response->assertInvalid("current_password");
$this->assertNull($admin->fresh()->pending_email);
Mail::assertNothingQueued();
}

private function passwordPayload(string $currentPassword = "old-password"): array
{
return [
"current_password" => $currentPassword,
"password" => "new-password-123",
"password_confirmation" => "new-password-123",
];
}

private function emailPayload(string $email = "new@example.com", string $currentPassword = "old-password"): array
{
return [
"email" => $email,
"current_password" => $currentPassword,
];
}
}
