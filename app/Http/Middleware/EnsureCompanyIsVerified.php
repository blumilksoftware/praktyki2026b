<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\CompanyVerificationStatus;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user === null
            || $user->status !== UserStatus::Active
            || $user->company === null
            || $user->company->verification_status !== CompanyVerificationStatus::Verified
        ) {
            abort(403);
        }

        return $next($request);
    }
}
