<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Actions\Auth\CreateCompanyAccount;
use App\DTO\Auth\CompanyRegistrationData;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Notifications\NewVerificationRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateCompanyAccountTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesCompanyAccountAndNotifiesSuperAdmins(): void
    {
        Notification::fake();

        $superAdmin = User::factory()->create(["role" => UserRole::SuperAdmin]);

        $action = new CreateCompanyAccount();
        $data = new CompanyRegistrationData(
            companyName: "Acme Sp. z o.o.",
            nip: "1234563218",
            email: "company@example.com",
            password: "Password123!",
            street: "Kwiatowa 1",
            postalCode: "00-001",
            city: "Warszawa",
            phone: "123456789",
            website: "https://acme.com",
        );

        $user = $action->execute($data);

        $this->assertEquals("company@example.com", $user->email);
        $this->assertEquals(UserRole::CompanyAdmin, $user->role);
        $this->assertEquals(UserStatus::Pending, $user->status);
        $this->assertNotNull($user->organization_id);

        $company = $user->company;
        $this->assertNotNull($company);
        $this->assertEquals("Acme Sp. z o.o.", $company->name);
        $this->assertEquals(VerificationStatus::Pending, $company->verification_status);

        Notification::assertSentTo(
            $superAdmin,
            NewVerificationRequestNotification::class,
            fn(NewVerificationRequestNotification $notification): bool => $notification->toArray($superAdmin)["entity_type"] === "company" &&
                $notification->toArray($superAdmin)["entity_id"] === $company->id,
        );
    }
}
