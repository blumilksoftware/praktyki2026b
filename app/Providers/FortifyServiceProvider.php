<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for("login", fn(Request $request) => Limit::perMinute(5)->by($this->emailAndIpKey($request)));

        RateLimiter::for("password-reset", fn(Request $request) => Limit::perMinutes(15, 5)->by($this->emailAndIpKey($request)));

        RateLimiter::for("email-verification", fn(Request $request) => Limit::perMinutes(15, 5)->by($this->emailAndIpKey($request)));

        RateLimiter::for("two-factor", fn(Request $request) => Limit::perMinute(5)->by($request->session()->get("login.id")));

        RateLimiter::for("passkeys", function (Request $request) {
            $credentialId = $request->input("credential.id");

            return Limit::perMinute(10)->by(
                ($credentialId ?: $request->session()->getId()) . "|" . $request->ip(),
            );
        });
    }

    private function emailAndIpKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input(Fortify::username())) . "|" . $request->ip());
    }
}
