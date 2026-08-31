<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeUserRoleAction;
use App\Actions\Admin\ChangeUserStatusAction;
use App\Actions\Admin\DeleteUserAction;
use App\Actions\Admin\SearchUsers;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchUsersRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(
        private readonly ChangeUserRoleAction $changeRoleAction,
        private readonly ChangeUserStatusAction $changeStatusAction,
        private readonly SearchUsers $searchUsers,
        private readonly DeleteUserAction $deleteUserAction,
    ) {}

    public function index(SearchUsersRequest $request): Response
    {
        $filters = $request->getData();

        return inertia("Admin/Users", [
            "users" => $this->searchUsers->execute($filters),
            "filters" => $filters,
            "roles" => array_map(fn(UserRole $role): string => $role->value, UserRole::cases()),
            "companies" => Company::query()->orderBy("name")->get(["id", "name"]),
            "universities" => University::query()->orderBy("name")->get(["id", "name"]),
            "meta" => [
                "title" => "Admin Users",
            ],
        ]);
    }

    public function updateRole(User $user, UpdateUserRoleRequest $request): RedirectResponse
    {
        Gate::authorize("updateRole", $user);

        $this->changeRoleAction->execute(Auth::user(), $user, $request->getRole(), $request->getOrganizationId());

        return back();
    }

    public function updateStatus(User $user, UpdateUserStatusRequest $request): RedirectResponse
    {
        Gate::authorize("updateStatus", $user);

        $this->changeStatusAction->execute(Auth::user(), $user, $request->getStatus());

        return back();
    }

    public function deleteUser(User $user): RedirectResponse
    {
        Gate::authorize("deleteUser", $user);

        $this->deleteUserAction->execute($user);

        return back();
    }
}
