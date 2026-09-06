<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Offer;
use App\Models\User;

class OfferPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::SuperAdmin && $user->status === UserStatus::Active) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return $user->status === UserStatus::Active
            && $user->company !== null;
    }

    public function update(User $user, Offer $offer): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $offer->company_id;
    }

    public function delete(User $user, Offer $offer): bool
    {
        return $user->status === UserStatus::Active
            && $user->organization_id === $offer->company_id;
    }
}
