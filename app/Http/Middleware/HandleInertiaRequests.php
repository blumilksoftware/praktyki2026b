<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Onboarding\GetProfileStepsAction;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = "app";

    public function __construct(
        private readonly GetProfileStepsAction $getProfileStepsAction,
    ) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            "auth" => fn() => $request->user() ? [
                "user" => $request->user(),
            ] : null,
            "onboarding" => fn() => $request->user() ? $this->onboardingData($request) : null,
            "flash" => [
                "requires_verification" => $request->session()->get("requires_verification"),
                "status" => $request->session()->get("status"),
            ],
        ];
    }

    private function onboardingData(Request $request): array
    {
        $user = $request->user();

        return [
            "show" => !$user->onboarding_dismissed_at && !$this->getProfileStepsAction->isComplete($user),
            "steps" => $this->getProfileStepsAction->execute($user),
        ];
    }
}
