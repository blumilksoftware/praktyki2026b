<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\University\ResolveUniversityByDomain;
use App\DTO\Auth\StudentRegistrationData;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\DB;

class CreateStudentAccount
{
    public function __construct(
        private readonly ResolveUniversityByDomain $resolveUniversityByDomain,
    ) {}

    public function execute(StudentRegistrationData $data): User
    {
        $university = $this->resolveUniversityByDomain->execute($data->email);

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

        app(EmailVerificationService::class)->sendVerificationEmail($user);

        return $user;
    }
}
