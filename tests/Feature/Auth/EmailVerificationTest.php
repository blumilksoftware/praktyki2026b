<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Mail\EmailVerificationMail;
use App\Mail\Verification\CompanyVerificationRejectMail;
use App\Models\Company;
use App\Models\EmailVerificationToken;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testVerifyWithValidTokenActivatesAccountAndRedirectsToLogin(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing("email_verification_tokens", ["user_id" => $user->id]);
    }

    public function testVerifyWithValidTokenInvalidatesItSoItCannotBeReused(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24),
        ]);

        $this->get("/email/verify/{$user->id}/{$plainToken}");

        $this->assertDatabaseMissing("email_verification_tokens", [
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
        ]);
    }

    public function testVerifyWithExpiredTokenDoesNotActivateAccount(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->subSecond(),
        ]);

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function testVerifyWithInvalidTokenDoesNotActivateAccount(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->get("/email/verify/{$user->id}/invalid-token");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function testVerifyAlreadyConfirmedAccountRedirectsToLoginWithoutError(): void
    {
        $user = User::factory()->create();

        $response = $this->get("/email/verify/{$user->id}/any-token");

        $response->assertRedirect("/login");
        $response->assertSessionMissing("errors");
    }

    public function testResendSendsNewVerificationEmail(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();

        $response = $this->post("/email/resend", ["email" => $user->email]);

        $response->assertRedirect();
        Mail::assertQueued(EmailVerificationMail::class, fn($mail): bool => $mail->hasTo($user->email));
    }

    public function testResendInvalidatesPreviousToken(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create();
        $oldToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $oldToken),
            "expires_at" => now()->addHours(24),
        ]);

        $this->post("/email/resend", ["email" => $user->email]);

        $this->assertDatabaseMissing("email_verification_tokens", [
            "user_id" => $user->id,
            "token" => hash("sha256", $oldToken),
        ]);

        $this->assertDatabaseCount("email_verification_tokens", 1);
    }

    public function testResendForAlreadyVerifiedUserDoesNotSendEmail(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->post("/email/resend", ["email" => $user->email]);

        $response->assertRedirect();
        Mail::assertNotSent(EmailVerificationMail::class);
    }

    public function testResendForNonExistentEmailDoesNotSendEmail(): void
    {
        Mail::fake();

        $response = $this->post("/email/resend", ["email" => "nonexistent@example.com"]);

        $response->assertRedirect();
        Mail::assertNotSent(EmailVerificationMail::class);
    }

    public function testResendRequiresEmailField(): void
    {
        $response = $this->post("/email/resend", []);

        $response->assertRedirect();
        $response->assertSessionHasErrors("email");
    }

    public function testTokenExpiresAfter24Hours(): void
    {
        $user = User::factory()->unverified()->create();
        $plainToken = Str::random(64);

        EmailVerificationToken::create([
            "user_id" => $user->id,
            "token" => hash("sha256", $plainToken),
            "expires_at" => now()->addHours(24)->subSecond(),
        ]);

        $this->travel(24)->hours();

        $response = $this->get("/email/verify/{$user->id}/{$plainToken}");

        $response->assertRedirect("/login");
        $response->assertSessionHasErrors("email");
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function testUniversityAfterApprovalAndLoginIsRedirectedToProfile(): void
    {
        Mail::fake();
        $superAdmin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $this->post(route("register.university"), [
            "university_name" => "Test University",
            "email" => "uni@example.com",
            "domain" => "example.edu",
            "password" => "password",
            "password_confirmation" => "password",
            "address" => "Some address",
            "phone" => "+48123456789",
            "website" => "https://example.edu",
            "terms" => "1",
        ]);
        $university = University::where("email", "uni@example.com")->firstOrFail();
        Mail::assertQueued(EmailVerificationMail::class);

        $this->actingAs($superAdmin)->post(route("admin.university.verify.accept", $university));
        Auth::logout();
        $response = $this->get(route("university.profile"));

        $response->assertRedirect("/login");
        $user = User::where("organization_id", $university->id)->firstOrFail();
        $response = $this->post(route("login"), [
            "email" => $user->email,
            "password" => "password",
        ]);
        $response->assertRedirect(route("university.profile"));
    }

    public function testCompanyAfterApprovalAndLogisIsRedirectedToProfile(): void
    {
        Mail::fake();
        $superAdmin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);
        $this->post(route("register.company"), [
            "company_name" => "Company",
            "password" => "password",
            "password_confirmation" => "password",
            "nip" => "6412502926",
            "email" => "company@example.com",
            "street" => "Street",
            "building_number" => "1A",
            "postal_code" => "00-111",
            "city" => "City",
            "phone" => "123456789",
            "website" => "https://mycompany.com",
            "terms" => "1",
        ]);

        $company = Company::where("email", "company@example.com")->firstOrFail();
        Mail::assertQueued(EmailVerificationMail::class);

        $this->actingAs($superAdmin)->post(route("admin.company.verify.accept", $company));
        Auth::logout();
        $response = $this->get(route("company.profile"));

        $response->assertRedirect("/login");
        $user = User::where("organization_id", $company->id)->firstOrFail();
        $response = $this->post(route("login"), [
            "email" => $user->email,
            "password" => "password",
        ]);
        $response->assertRedirect(route("company.profile"));
    }

    public function testCompanyRejectionSendsMailToCompany(): void
    {
        Mail::fake();

        $superAdmin = User::factory()->create([
            "role" => UserRole::SuperAdmin,
        ]);

        $rejectionReason = "Rejection message";

        $company = Company::factory()->pending()->create();

        $this->actingAs($superAdmin)->post(route("admin.company.verify.reject", $company), [
            "rejection_reason" => $rejectionReason,
        ]);

        Mail::assertQueued(CompanyVerificationRejectMail::class, 1);
        Mail::assertQueued(CompanyVerificationRejectMail::class, fn(CompanyVerificationRejectMail $mail): bool => $mail->hasTo($company->email) && $mail->rejectionReason === $rejectionReason);
    }
}
