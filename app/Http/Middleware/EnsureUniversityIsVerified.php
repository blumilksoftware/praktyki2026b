<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UniversityVerificationStatus;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUniversityIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user === null
            || $user->status !== UserStatus::Active
            || $user->universityOrganization === null
            || $user->universityOrganization->verification_status !== UniversityVerificationStatus::Verified
        ) {
            abort(403);
        }

        return $next($request);
    }
}
