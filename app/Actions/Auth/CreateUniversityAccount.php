<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\UniversityRegistrationData;
use App\Enums\UniversityVerificationStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUniversityAccount
{
    public function execute(UniversityRegistrationData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $university = University::create([
                "name" => $data->universityName,
                "email" => $data->email,
                "domain" => $data->domain,
                "street" => $data->street,
                "building_number" => $data->buildingNumber,
                "postal_code" => $data->postalCode,
                "city" => $data->city,
                "phone" => $data->phone,
                "website" => $data->website,
                "verification_status" => UniversityVerificationStatus::Pending,
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
    }
}
