<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NipRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nip = preg_replace("/\D/", "", (string)$value);

        if (!preg_match("/^\d{10}$/", $nip) || $nip === "0000000000") {
            $fail("validation.nip")->translate();

            return;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$nip[$i] * $weights[$i];
        }

        $checksum = $sum % 11;

        if ($checksum === 10 || $checksum !== (int)$nip[9]) {
            $fail("validation.nip")->translate();
        }
    }
}
