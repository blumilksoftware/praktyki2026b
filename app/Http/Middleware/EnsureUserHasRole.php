<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        foreach ($roles as $role) {
            $resolved = UserRole::tryFrom($role);

            if ($resolved === null) {
                throw new InvalidArgumentException("Unknown role: {$role}");
            }

            if ($user->role === $resolved) {
                return $next($request);
            }
        }

        abort(403);
    }
}
