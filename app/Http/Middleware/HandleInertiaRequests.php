<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Onboarding\GetProfileStepsAction;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
            "locale" => app()->getLocale(),
            "auth" => fn() => $request->user() ? [
                "user" => $request->user()->loadMissing(["company", "universityOrganization"]),
            ] : null,
            "favoriteOfferIds" => fn() => $request->user()?->role === UserRole::Student ? $request->user()->favourites()->pluck("offers.id")->all() : [],
            "onboarding" => fn() => $request->user() ? $this->onboardingData($request) : null,
            "notificationsUnreadCount" => fn() => $request->user()?->unreadNotifications()->count() ?? 0,
            "notifications" => Inertia::optional(fn() => $request->user()
                ? $request->user()->notifications()->latest()->limit(20)->get()->map(fn($notification): array => [
                    "id" => $notification->id,
                    "type" => $notification->data["type"] ?? null,
                    "data" => $notification->data,
                    "read_at" => $notification->read_at,
                    "created_at" => $notification->created_at,
                ])->all()
                : []),
            "flash" => [
                "requires_verification" => $request->session()->get("requires_verification"),
                "status" => $request->session()->get("status"),
                "error" => $request->session()->get("error"),
                "email" => $request->session()->get("email"),
            ],
            "support_email" => config("mail.support_email"),
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
