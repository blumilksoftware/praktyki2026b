<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePassword
{
    public function execute(User $student, string $password): void
    {
        $student->forceFill([
            "password" => Hash::make($password),
        ])->save();
    }
}
