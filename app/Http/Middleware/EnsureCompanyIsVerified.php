<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->status === UserStatus::Pending) {
            return redirect()->route("company.verification.pending");
        }

        Gate::forUser($user)->authorize("access-company-panel");

        if ($user->company->verification_status !== VerificationStatus::Verified) {
            return redirect()->route("company.verification.pending");
        }

        return $next($request);
    }
}