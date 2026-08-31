<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class ScrubInvalidUtf8 extends TransformsRequest
{
    protected function transform(mixed $key, mixed $value): mixed
    {
        if (!is_string($value) || mb_check_encoding($value, "UTF-8")) {
            return $value;
        }

        return mb_scrub($value);
    }
}
