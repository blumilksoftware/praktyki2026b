<?php

declare(strict_types=1);

namespace App\Actions\Onboarding;

use App\DTO\Onboarding\ProfileStep;
use App\Enums\UserRole;
use App\Models\User;

class GetProfileStepsAction
{
    /**
     * @return array<ProfileStep>
     */
    public function execute(User $user): array
    {
        return match ($user->role) {
            UserRole::Student => [
                new ProfileStep("university", filled($user->university)),
                new ProfileStep("cv", filled($user->cv_path)),
            ],
            UserRole::CompanyAdmin => [
                new ProfileStep("logo", filled($user->company?->logo_path)),
                new ProfileStep("description", filled($user->company?->description)),
                new ProfileStep("tags", filled($user->company?->tags)),
            ],
            UserRole::UniversityAdmin => [
                new ProfileStep("logo", filled($user->universityOrganization?->logo_path)),
                new ProfileStep("externalFormUrl", filled($user->universityOrganization?->external_form_url)),
            ],
            default => [],
        };
    }

    public function isComplete(User $user): bool
    {
        $steps = $this->execute($user);

        return $steps === [] || collect($steps)->every(fn(ProfileStep $step) => $step->completed);
    }
}
