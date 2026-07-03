<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BuildingNumberRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match("/^\d+[A-Za-z]?(?:\/\d+[A-Za-z]?)?$/", (string)$value)) {
            $fail("validation.building_number_format")->translate();
        }
    }
}
