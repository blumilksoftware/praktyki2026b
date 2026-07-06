<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = "app";

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            "locale" => app()->getLocale(),
            "flash" => [
                "requires_verification" => $request->session()->get("requires_verification"),
                "status" => $request->session()->get("status"),
            ],
        ];
    }
}
