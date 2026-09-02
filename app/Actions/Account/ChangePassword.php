<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePassword
{
public function execute(User $user, string $password): void
{
$user->forceFill([
"password" => Hash::make($password),
])->save();
}
}
