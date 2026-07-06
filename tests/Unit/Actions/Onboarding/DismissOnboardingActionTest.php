<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Onboarding;

use App\Actions\Onboarding\DismissOnboardingAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DismissOnboardingActionTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteSetsOnboardingDismissedAt(): void
    {
        $user = User::factory()->create(["onboarding_dismissed_at" => null]);

        (new DismissOnboardingAction())->execute($user);

        $this->assertNotNull($user->refresh()->onboarding_dismissed_at);
    }

    public function testExecuteIsIdempotent(): void
    {
        $dismissedAt = now()->subDay();
        $user = User::factory()->create(["onboarding_dismissed_at" => $dismissedAt]);

        (new DismissOnboardingAction())->execute($user);

        $this->assertNotNull($user->refresh()->onboarding_dismissed_at);
    }
}
