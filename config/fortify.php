<?php

declare(strict_types=1);

return [
    "guard" => "web",
    "passwords" => "users",
    "username" => "email",
    "email" => "email",
    "lowercase_usernames" => true,
    "home" => "/home",
    "prefix" => "",
    "domain" => null,
    "middleware" => ["web"],
    "limiters" => [
        "login" => "login",
    ],
    "views" => true,
    "passkeys" => [
        "relying_party_id" => parse_url(config("app.url"), PHP_URL_HOST),
        "allowed_origins" => [config("app.url")],
        "timeout" => 60000,
    ],
    "features" => [],
];
