<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTO\Auth\StudentRegistrationData;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateStudentAccount
{
    public function execute(StudentRegistrationData $data): User
    {
        return DB::transaction(fn(): User => User::create([
            "first_name" => $data->firstName,
            "last_name" => $data->lastName,
            "email" => $data->email,
            "password" => $data->password,
            "role" => UserRole::Student,
            "university" => $data->university,
            "terms_accepted_at" => now(),
        ]));
    }
}
