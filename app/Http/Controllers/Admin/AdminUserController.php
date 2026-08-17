<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeUserRoleAction;
use App\Actions\Admin\ChangeUserStatusAction;
use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly ChangeUserRoleAction $changeRoleAction,
        private readonly ChangeUserStatusAction $changeStatusAction,
    ) {}

    public function index(Request $request): Response
    {
        $roleFilter = $request->query("role", "all");

        if (!is_string($roleFilter) || !mb_check_encoding($roleFilter, "UTF-8")) {
            $roleFilter = "all";
        }

        $searchQuery = $request->query("search", "");

        if (!is_string($searchQuery) || !mb_check_encoding($searchQuery, "UTF-8")) {
            $searchQuery = "";
        }

        $usersQuery = User::query();

        if ($roleFilter !== "all") {
            $usersQuery->where("role", $roleFilter);
        }

        if ($searchQuery) {
            $usersQuery->where(function ($q) use ($searchQuery): void {
                $q->where("first_name", "like", "%{$searchQuery}%")
                    ->orWhere("last_name", "like", "%{$searchQuery}%")
                    ->orWhere("email", "like", "%{$searchQuery}%");
            });
        }

        $users = $usersQuery->orderBy("created_at", "desc")->paginate(20)->appends([
            "role" => $roleFilter,
            "search" => $searchQuery,
        ]);

        return inertia("Admin/Users", [
            "users" => $users,
            "filters" => [
                "role" => $roleFilter,
                "search" => $searchQuery,
            ],
            "roles" => array_map(fn(UserRole $role): string => $role->value, UserRole::cases()),
            "companies" => Company::query()->orderBy("name")->get(["id", "name"]),
            "universities" => University::query()->orderBy("name")->get(["id", "name"]),
            "meta" => [
                "title" => "Admin Users",
            ],
        ]);
    }

    public function updateRole(User $user, Request $request): RedirectResponse
    {
        Gate::authorize("updateRole", $user);

        $role = UserRole::tryFrom((string)$request->input("role"));

        $organizationRules = match ($role?->organizationType()) {
            OrganizationType::Company => ["required", "exists:companies,id"],
            OrganizationType::University => ["required", "exists:universities,id"],
            null => ["nullable"],
        };

        $validated = $request->validate([
            "role" => ["required", Rule::enum(UserRole::class)],
            "organization_id" => $organizationRules,
        ]);

        $this->changeRoleAction->execute(
            Auth::user(),
            $user,
            UserRole::from($validated["role"]),
            $validated["organization_id"] ?? null,
        );

        return back();
    }

    public function updateStatus(User $user, Request $request): RedirectResponse
    {
        Gate::authorize("updateStatus", $user);

        $validated = $request->validate([
            "status" => ["required", Rule::in([UserStatus::Active->value, UserStatus::Blocked->value])],
        ]);

        $this->changeStatusAction->execute(Auth::user(), $user, UserStatus::from($validated["status"]));

        return back();
    }
}
