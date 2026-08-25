<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DeletionImpactController extends Controller
{
    public function __construct(
        private readonly UserPolicy $userPolicy,
    ) {}

    public function __invoke(User $user): JsonResponse
    {
        Gate::authorize("deleteUser", $user);

        return response()->json([
            "isLastOrganizationMember" => $this->userPolicy->isLastOrganizationMember($user),
        ]);
    }
}
