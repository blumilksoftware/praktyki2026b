<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\OfferPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Offer::class, OfferPolicy::class);

        Gate::define("access-student-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->role === UserRole::Student);

        Gate::define("access-company-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->company !== null);

        Gate::define("access-university-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->universityOrganization !== null);
    }
}
