<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UniqueNameRule implements ValidationRule
{
    public function __construct(
        protected Builder $scope,
        protected string $messageKey,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $taken = $this->scope
            ->clone()
            ->whereRaw("LOWER(name) = ?", [Str::lower($value)])
            ->exists();

        if ($taken) {
            $fail($this->messageKey)->translate();
        }
    }
}
