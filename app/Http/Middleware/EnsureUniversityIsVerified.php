<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\VerificationStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureUniversityIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        Gate::forUser($request->user())->authorize("access-university-panel");

        if ($request->user()->universityOrganization->verification_status !== VerificationStatus::Verified) {
            return redirect()->route("university.verification.pending");
        }

        return $next($request);
    }
}
