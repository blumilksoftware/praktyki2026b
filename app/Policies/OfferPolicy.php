<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\User;

class OfferPolicy
{
    public function create(User $user): bool
    {
        return $user->status === UserStatus::Active && $user->company !== null;
    }
}
