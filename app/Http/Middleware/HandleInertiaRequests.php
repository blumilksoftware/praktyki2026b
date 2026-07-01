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
            "flash" => [
                "requires_verification" => $request->session()->get("requires_verification"),
                "status" => $request->session()->get("status"),
            ],
            "validation" => [
                "messages" => [
                    "required" => __("validation.required"),
                    "email" => __("validation.email"),
                    "confirmed" => __("validation.confirmed"),
                    "accepted" => __("validation.accepted"),
                    "nip" => __("validation.nip"),
                    "url" => __("validation.url"),
                ],
                "attributes" => __("validation.attributes"),
            ],
        ];
    }
}
