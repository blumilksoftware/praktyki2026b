<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function downloadCv(User $user, Application $application): bool
    {
        $application->loadMissing(["offer", "student"]);

        return $application->offer->company_id === $user->company?->id;
    }
}
