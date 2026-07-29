<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\UniversityRegistrationData;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\University;
use App\Models\User;
use App\Notifications\NewVerificationRequestNotification;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateUniversityAccount
{
    public function execute(UniversityRegistrationData $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $university = University::create([
                "name" => $data->universityName,
                "email" => $data->email,
                "domain" => $data->domain,
                "street" => $data->street,
                "postal_code" => $data->postalCode,
                "city" => $data->city,
                "phone" => $data->phone,
                "website" => $data->website,
                "verification_status" => VerificationStatus::Pending,
            ]);

            return User::create([
                "email" => $data->email,
                "password" => $data->password,
                "role" => UserRole::UniversityAdmin,
                "status" => UserStatus::Pending,
                "organization_id" => $university->id,
                "terms_accepted_at" => now(),
            ]);
        });

        app(EmailVerificationService::class)->sendVerificationEmail($user);

        Notification::send(
            User::where("role", UserRole::SuperAdmin)->get(),
            new NewVerificationRequestNotification($user->universityOrganization),
        );

        return $user;
    }
}
