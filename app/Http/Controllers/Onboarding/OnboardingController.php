<?php

declare(strict_types=1);

namespace App\Http\Controllers\Onboarding;

use App\Actions\Onboarding\DismissOnboardingAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly DismissOnboardingAction $dismissOnboardingAction,
    ) {}

    public function dismiss(): RedirectResponse
    {
        $this->dismissOnboardingAction->execute(Auth::user());

        return back();
    }
}
