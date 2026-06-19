<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = (string)$value;

        if (!preg_match("/^(?:\+48|0048|48)? ?[1-9]\d{2}(?:[ -]?\d{3}){2}$/", $phone)) {
            $fail("validation.phone")->translate();
        }
    }
}
