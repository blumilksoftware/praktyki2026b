<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Actions\Auth\CreateUniversityAccount;
use App\DTO\Auth\UniversityRegistrationData;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateUniversityAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesUniversityAccountAndProfileWithCorrectFields(): void
    {
        Notification::fake();

        $action = new CreateUniversityAccount();
        $data = new UniversityRegistrationData(
            universityName: "Example University",
            email: "university@example.com",
            domain: "example.com",
            password: "Password123!",
            address: "123 Academic Street, City",
            phone: "123456789",
            website: "https://example.com",
        );

        $user = $action->execute($data);

        $this->assertEquals("university@example.com", $user->email);
        $this->assertEquals(UserRole::UniversityAdmin, $user->role);
        $this->assertEquals(UserStatus::Pending, $user->status);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->organization_id);

        $university = $user->universityOrganization;
        $this->assertNotNull($university);
        $this->assertEquals("Example University", $university->name);
        $this->assertEquals("university@example.com", $university->email);
        $this->assertEquals("example.com", $university->domain);
        $this->assertEquals("123 Academic Street, City", $university->address);
        $this->assertEquals("123456789", $university->phone);
        $this->assertEquals("https://example.com", $university->website);
        $this->assertEquals(VerificationStatus::Pending, $university->verification_status);
    }
}
