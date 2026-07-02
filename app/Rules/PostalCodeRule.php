<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PostalCodeRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match("/^\d{2}-\d{3}$/", (string)$value)) {
            $fail("validation.postal_code_format")->translate();
        }
    }
}
