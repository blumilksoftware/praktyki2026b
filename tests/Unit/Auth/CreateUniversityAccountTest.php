<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Actions\Auth\CreateUniversityAccount;
use App\DTO\Auth\UniversityRegistrationData;
use App\Enums\UniversityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
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
            universityName: "Politechnika Przykładowa",
            email: "uczelnia@example.com",
            domain: "example.com",
            password: "Password123!",
            address: "ul. Akademicka 1, 00-001 Warszawa",
            phone: "123456789",
            website: "https://example.com",
        );

        $user = $action->execute($data);

        $this->assertEquals("uczelnia@example.com", $user->email);
        $this->assertEquals(UserRole::UniversityAdmin, $user->role);
        $this->assertEquals(UserStatus::Pending, $user->status);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->organization_id);

        $university = $user->universityOrganization;
        $this->assertNotNull($university);
        $this->assertEquals("Politechnika Przykładowa", $university->name);
        $this->assertEquals("uczelnia@example.com", $university->email);
        $this->assertEquals("example.com", $university->domain);
        $this->assertEquals("ul. Akademicka 1, 00-001 Warszawa", $university->address);
        $this->assertEquals("123456789", $university->phone);
        $this->assertEquals("https://example.com", $university->website);
        $this->assertEquals(UniversityVerificationStatus::Pending, $university->verification_status);
    }
}
