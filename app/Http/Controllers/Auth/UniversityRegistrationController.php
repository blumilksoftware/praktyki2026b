<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateUniversityAccount;
use App\DTO\Auth\UniversityRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UniversityRegistrationRequest;
use Illuminate\Http\RedirectResponse;

class UniversityRegistrationController extends Controller
{
    public function __construct(
        private readonly CreateUniversityAccount $createUniversityAccount,
    ) {}

    public function __invoke(UniversityRegistrationRequest $request): RedirectResponse
    {
        $data = UniversityRegistrationData::fromArray($request->validated());
        $user = $this->createUniversityAccount->execute($data);

        return redirect()->route("verification.waiting")->with("email", $user->email);
    }
}
