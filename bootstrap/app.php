<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsNotBlocked;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            EnsureUserIsNotBlocked::class,
        ]);
        $middleware->trustProxies(at: "*");
        $middleware->alias([
            "role" => EnsureUserHasRole::class,
        ]);

        $middleware->redirectUsersTo(function (): string {
            return match (auth()->user()?->role) {
                UserRole::SuperAdmin => route("admin.dashboard"),
                UserRole::CompanyAdmin, UserRole::CompanyMember => route("company.dashboard"),
                UserRole::UniversityAdmin, UserRole::UniversityMember => route("university.dashboard"),
                UserRole::Student => route("student.dashboard"),
                default => route("login"),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $_, Request $request): Response {
            $status = $response->getStatusCode();

            if ($status === 429 && !$request->expectsJson()) {
                $seconds = $response->headers->get("Retry-After");
                $message = __("auth.throttle", ["seconds" => $seconds]);

                return ($request->has("email")
                    ? back()->withErrors(["email" => $message])
                    : back()->with("error", $message))
                    ->withHeaders(["Retry-After" => $seconds]);
            }

            if (!in_array($status, [401, 403, 404, 413, 500], true)) {
                return $response;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    "message" => Response::$statusTexts[$status] ?? "Error",
                ], $status);
            }

            return Inertia::render("Errors/Error", [
                "status" => $status,
                "role" => $request->user()?->role?->value,
            ])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
