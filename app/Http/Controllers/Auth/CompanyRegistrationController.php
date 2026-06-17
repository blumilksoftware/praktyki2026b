<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateCompanyAccount;
use App\DTO\Auth\CompanyRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegistrationRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class CompanyRegistrationController extends Controller
{
    public function __invoke(CompanyRegistrationRequest $request, CreateCompanyAccount $action): JsonResponse
    {
        $user = $action->execute(CompanyRegistrationData::fromArray($request->getData()));

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
