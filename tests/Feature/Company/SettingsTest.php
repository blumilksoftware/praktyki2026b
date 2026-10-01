<?php

declare(strict_types=1);

namespace Tests\Feature\Company;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\EmailChangeVerificationMail;
use App\Models\Company;
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
        $this->get(route("company.settings"))->assertRedirect(route("login"));
        $this->put(route("company.password.update"), $this->passwordPayload())->assertRedirect(route("login"));
        $this->patch(route("company.email.update"), $this->emailPayload())->assertRedirect(route("login"));
        $this->delete(route("company.account.delete"), $this->deletePayload())->assertRedirect(route("login"));
    }

    public function testNonCompanyRoleCannotAccessSettingsOrMutateAccount(): void
    {
        $student = User::factory()->create(["role" => UserRole::Student, "status" => UserStatus::Active]);

        $this->actingAs($student)->get(route("company.settings"))->assertStatus(403);
        $this->actingAs($student)->put(route("company.password.update"), $this->passwordPayload())->assertStatus(403);
        $this->actingAs($student)->patch(route("company.email.update"), $this->emailPayload())->assertStatus(403);
        $this->actingAs($student)->delete(route("company.account.delete"), $this->deletePayload())->assertStatus(403);
    }

    public function testUnverifiedCompanyAdminCanStillAccessSettings(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $this->actingAs($admin)
            ->get(route("company.settings"))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component("Company/Settings"));
    }

    public function testCompanyAdminCanSeeSettingsPage(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "email" => "company@example.com",
        ]);

        $this->actingAs($admin)
            ->get(route("company.settings"))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component("Company/Settings")
                    ->where("email", "company@example.com"),
            );
    }

    public function testCompanyMemberCanChangePasswordWithCorrectCurrentPassword(): void
    {
        $company = Company::factory()->approved()->create();
        User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($member)->put(route("company.password.update"), $this->passwordPayload("old-password"));

        $response->assertRedirect();
        $this->assertTrue(Hash::check("new-password-123", $member->fresh()->password));
    }

    public function testChangePasswordFailsWithWrongCurrentPassword(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->put(route("company.password.update"), $this->passwordPayload("wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertTrue(Hash::check("old-password", $admin->fresh()->password));
    }

    public function testCompanyAdminCanRequestEmailChangeWithCorrectPassword(): void
    {
        Mail::fake();
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->patch(route("company.email.update"), $this->emailPayload("new@example.com", "old-password"));

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
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($admin)->patch(route("company.email.update"), $this->emailPayload("new@example.com", "wrong-password"));

        $response->assertInvalid("current_password");
        $this->assertNull($admin->fresh()->pending_email);
        Mail::assertNothingQueued();
    }

    public function testCompanyMemberCanDeleteTheirOwnAccount(): void
    {
        $company = Company::factory()->approved()->create();
        User::factory()->companyAdmin()->create(["organization_id" => $company->id]);
        $member = User::factory()->companyMember()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);

        $response = $this->actingAs($member)->delete(route("company.account.delete"), $this->deletePayload());

        $response->assertRedirect("/");
        $this->assertModelMissing($member);
        $this->assertGuest();
    }

    public function testCompanyAdminCanDeleteAccountWhenAnotherAdminRemains(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);
        User::factory()->companyAdmin()->create(["organization_id" => $company->id]);

        $response = $this->actingAs($admin)->delete(route("company.account.delete"), $this->deletePayload());

        $response->assertRedirect("/");
        $this->assertModelMissing($admin);
    }

    public function testSoleCompanyAdminCannotDeleteAccountAndOrphanTheCompany(): void
    {
        $company = Company::factory()->approved()->create();
        $admin = User::factory()->companyAdmin()->create([
            "organization_id" => $company->id,
            "password" => "old-password",
        ]);
        User::factory()->companyMember()->create(["organization_id" => $company->id]);

        $response = $this->actingAs($admin)->delete(route("company.account.delete"), $this->deletePayload());

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
