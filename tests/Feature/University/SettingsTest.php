<?php

declare(strict_types=1);

namespace Tests\Feature\University;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\EmailChangeVerificationMail;
use App\Models\University;
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
        $this->get(route("university.settings"))->assertRedirect(route("login"));
        $this->put(route("university.password.update"), $this->passwordPayload())->assertRedirect(route("login"));
        $this->patch(route("university.email.update"), $this->emailPayload())->assertRedirect(route("login"));
        $this->delete(route("university.account.delete"), $this->deletePayload())->assertRedirect(route("login"));
    }

    public function testNonUniversityRoleCannotAccessSettingsOrMutateAccount(): void
    {
        $student = User::factory()->create(["role" => UserRole::Student, "status" => UserStatus::Active]);

        $this->actingAs($student)->get(route("university.settings"))->assertStatus(403);
        $this->actingAs($student)->put(route("university.password.update"), $this->passwordPayload())->assertStatus(403);
        $this->actingAs($student)->patch(route("university.email.update"), $this->emailPayload())->assertStatus(403);
        $this->actingAs($student)->delete(route("university.account.delete"), $this->deletePayload())->assertStatus(403);
    }

    public function testUnverifiedUniversityAdminCanStillAccessSettings(): void
    {
        $university = University::factory()->create();
        $admin = User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $this->actingAs($admin)
            ->get(route("university.settings"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component("University/Settings"));
    }

    public function testUniversityAdminCanSeeSettingsPage(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "email" => "university@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("university.settings"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("University/Settings")
                    ->where("email", "university@example.com"),
            );
    }

    public function testUniversityMemberCanChangePasswordWithCorrectCurrentPassword(): void
    {
        $university = University::factory()->approved()->create();
        User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $member = User::factory()->universityMember()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($member)->put(route("university.password.update"), $this->passwordPayload("old-password"));

        $response->assertRedirect();
        $this->assertTrue(Hash::check("new-password-123", $member->fresh()->password));
    }

    public function testChangePasswordFailsWithWrongCurrentPassword(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->put(route("university.password.update"), $this->passwordPayload("wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertTrue(Hash::check("old-password", $admin->fresh()->password));
    }

    public function testUniversityAdminCanRequestEmailChangeWithCorrectPassword(): void
    {
        Mail::fake();
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->patch(route("university.email.update"), $this->emailPayload("new@example.com", "old-password"));

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
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->patch(route("university.email.update"), $this->emailPayload("new@example.com", "wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertNull($admin->fresh()->pending_email);
        Mail::assertNothingQueued();
    }

    public function testUniversityMemberCanDeleteTheirOwnAccount(): void
    {
        $university = University::factory()->approved()->create();
        User::factory()->universityAdmin()->create(["organization_id" => $university->id]);
        $member = User::factory()->universityMember()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($member)->delete(route("university.account.delete"), $this->deletePayload());

        $response->assertRedirect("/");
        $this->assertModelMissing($member);
        $this->assertGuest();
    }

    public function testUniversityAdminCanDeleteAccountWhenAnotherAdminRemains(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);
        User::factory()->universityAdmin()->create(["organization_id" => $university->id]);

        $response = $this->actingAs($admin)->delete(route("university.account.delete"), $this->deletePayload());

        $response->assertRedirect("/");
        $this->assertModelMissing($admin);
    }

    public function testSoleUniversityAdminCannotDeleteAccountAndOrphanTheUniversity(): void
    {
        $university = University::factory()->approved()->create();
        $admin = User::factory()->universityAdmin()->create([
            "organization_id" => $university->id,
            "password" => "old-password",
        ]);
        User::factory()->universityMember()->create(["organization_id" => $university->id]);

        $response = $this->actingAs($admin)->delete(route("university.account.delete"), $this->deletePayload());

        $response->assertSessionHasErrors("member");
        $this->assertModelExists($admin);
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

    private function deletePayload(string $password = "old-password"): array
    {
        return [
            "password" => $password,
            "confirmation" => true,
        ];
    }
}
