<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateUniversityAccount;
use App\DTO\Auth\UniversityRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UniversityRegistrationRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UniversityRegistrationController extends Controller
{
    public function __invoke(UniversityRegistrationRequest $request, CreateUniversityAccount $action): JsonResponse
    {
        $user = $action->execute(UniversityRegistrationData::fromArray($request->getData()));

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
