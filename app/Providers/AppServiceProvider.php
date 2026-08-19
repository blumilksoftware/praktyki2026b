<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Faculty;
use App\Models\Offer;
use App\Models\OrganizationInvitation;
use App\Models\StudyField;
use App\Models\User;
use App\Observers\OfferObserver;
use App\Policies\ApplicationPolicy;
use App\Policies\FacultyPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OfferPolicy;
use App\Policies\OrganizationInvitationPolicy;
use App\Policies\StudyFieldPolicy;
use App\Policies\UserPolicy;
use Illuminate\Notifications\DatabaseNotification;
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
        Gate::policy(Faculty::class, FacultyPolicy::class);
        Gate::policy(StudyField::class, StudyFieldPolicy::class);
        Gate::policy(Offer::class, OfferPolicy::class);
        Gate::policy(OrganizationInvitation::class, OrganizationInvitationPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Offer::observe(OfferObserver::class);

        Gate::define("access-student-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->role === UserRole::Student);
        Gate::define("access-company-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->company !== null);
        Gate::define("access-university-panel", fn(User $user): bool => $user->status === UserStatus::Active && $user->universityOrganization !== null);
    }
}
