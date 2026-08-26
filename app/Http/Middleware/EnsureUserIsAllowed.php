<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Auth\LogOutUser;
use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAllowed
{
    public function __construct(
        private readonly LogOutUser $logOutUser,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ($user->status === UserStatus::Blocked || $user->status === UserStatus::Deleted)) {
            $message = $user->status === UserStatus::Blocked ? "auth.blocked" : "auth.deleted";

            $this->logOutUser->execute($request);

            if ($request->expectsJson()) {
                return response()->json([
                    "message" => __($message),
                ], 403);
            }

            return redirect()->route("login")->withErrors([
                "email" => __($message),
            ]);
        }

        return $next($request);
    }
}
