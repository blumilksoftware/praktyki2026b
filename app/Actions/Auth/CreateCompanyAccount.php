<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\CompanyRegistrationData;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCompanyAccount
{
    public function execute(CompanyRegistrationData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $company = Company::create([
                "name" => $data->companyName,
                "nip" => preg_replace("/\D/", "", $data->nip),
                "email" => $data->email,
                "street" => $data->street,
                "building_number" => $data->buildingNumber,
                "postal_code" => $data->postalCode,
                "city" => $data->city,
                "phone" => $data->phone,
                "website" => $data->website,
                "verification_status" => VerificationStatus::Pending,
            ]);

            $user = User::create([
                "email" => $data->email,
                "password" => $data->password,
                "role" => UserRole::CompanyAdmin,
                "status" => UserStatus::Pending,
                "organization_id" => $company->id,
                "terms_accepted_at" => now(),
            ]);
            $user->sendEmailVerificationNotification();

            return $user;
        });
    }
}
