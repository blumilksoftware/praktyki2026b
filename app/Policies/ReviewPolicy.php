<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->status === UserStatus::Active;
    }

    public function hide(User $user, Review $review): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $review->company_id;
    }
}
