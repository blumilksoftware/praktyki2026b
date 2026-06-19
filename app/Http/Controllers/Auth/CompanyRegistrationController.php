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
    public function __construct(
        private readonly CreateCompanyAccount $createCompanyAccount,
    ) {}

    public function __invoke(CompanyRegistrationRequest $request): RedirectResponse
    {
        $data = CompanyRegistrationData::fromArray($request->getData());
        $this->createCompanyAccount->execute($data);

        return redirect()->route("login")->with("status", __("auth.register.company"));
    }
}
