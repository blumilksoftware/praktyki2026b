<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleInertiaRequests;
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
            HandleInertiaRequests::class,
        ]);
        $middleware->trustProxies(at: "*");
        $middleware->alias([
            "role" => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $_, Request $request): Response {
            $status = $response->getStatusCode();

            if (!in_array($status, [401, 403, 404, 500], true)) {
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
