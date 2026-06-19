<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\StudentRegistrationData;
use App\Enums\UserRole;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateStudentAccount
{
    public function execute(StudentRegistrationData $data): User
    {
        $domain = $this->extractDomainFromEmail($data->email);
        $university = $this->resolveUniversity($domain);

        $user = DB::transaction(fn(): User => User::create([
            "first_name" => $data->firstName,
            "last_name" => $data->lastName,
            "email" => $data->email,
            "password" => $data->password,
            "role" => UserRole::Student,
            "university" => $data->university,
            "organization_id" => $university?->id,
            "terms_accepted_at" => now(),
        ]));

        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function extractDomainFromEmail(string $email): string
    {
        return substr($email, strrpos($email, "@") + 1);
    }

    public function resolveUniversity(string $domain): ?University
    {
        if ($university = University::where("domain", $domain)->first()) {
            return $university;
        }

        $parts = explode(".", $domain);

        while (count($parts) > 1) {
            array_shift($parts);
            $parentDomain = implode(".", $parts);

            if ($university = University::where("domain", $parentDomain)->first()) {
                return $university;
            }
        }

        return null;
    }
}
