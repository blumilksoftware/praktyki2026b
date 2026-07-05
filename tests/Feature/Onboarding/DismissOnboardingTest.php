<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DismissOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCannotDismiss(): void
    {
        $this->post(route("onboarding.dismiss"))
            ->assertRedirect(route("login"));
    }

    public function testAuthenticatedUserCanDismiss(): void
    {
        $user = User::factory()->create(["onboarding_dismissed_at" => null]);

        $this->actingAs($user)
            ->post(route("onboarding.dismiss"))
            ->assertRedirect();

        $this->assertNotNull($user->refresh()->onboarding_dismissed_at);
    }

    public function testDismissIsIdempotent(): void
    {
        $user = User::factory()->create(["onboarding_dismissed_at" => now()->subDay()]);

        $this->actingAs($user)
            ->post(route("onboarding.dismiss"))
            ->assertRedirect();

        $this->assertNotNull($user->refresh()->onboarding_dismissed_at);
    }
}
