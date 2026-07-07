<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\Models\User;

class DismissOnboardingAction
{
    public function execute(User $user): void
    {
        $user->update([
            "onboarding_dismissed_at" => now(),
        ]);
    }
}
