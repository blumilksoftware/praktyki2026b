<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\HandleGoogleCallback;
use App\DTO\Auth\GoogleUserData;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Mail\StudentRegistrationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthController extends Controller
{
    public function __construct(
        private readonly HandleGoogleCallback $handleGoogleCallback,
    ) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver("google")->redirect();
    }

    public function callback(): RedirectResponse
    {
        $socialiteUser = Socialite::driver("google")->user();
        $data = GoogleUserData::fromSocialite($socialiteUser);
        $user = $this->handleGoogleCallback->execute($data);

        if ($user->wasRecentlyCreated) {
            Mail::to($user->email)->queue(new StudentRegistrationMail($user));
        }

        if ($user->status === UserStatus::Pending) {
            session()->put("email", $user->email);

            return redirect()->route("verification.waiting");
        }

        auth()->login($user);

        return redirect()->intended("/");
    }
}
