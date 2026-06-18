<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateCompanyAccount;
use App\DTO\Auth\CompanyRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegistrationRequest;
use Illuminate\Http\RedirectResponse;

class CompanyRegistrationController extends Controller
{
    public function __invoke(CompanyRegistrationRequest $request, CreateCompanyAccount $action): RedirectResponse
    {
        $user = $action->execute(CompanyRegistrationData::fromArray($request->getData()));

        return redirect("/login");
    }
}
