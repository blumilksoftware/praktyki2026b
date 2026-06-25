<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class MimeTypeByContentRule implements ValidationRule
{
    /**
     * @param array<string> $allowedMimes
     */
    public function __construct(
        protected array $allowedMimes,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail("validation.mime_type_by_content")->translate([
                "attribute" => $attribute,
                "values" => implode(", ", $this->allowedMimes),
            ]);

            return;
        }

        $mimeType = $value->getMimeType();

        if (!in_array($mimeType, $this->allowedMimes, true)) {
            $fail("validation.mime_type_by_content")->translate([
                "attribute" => $attribute,
                "values" => implode(", ", $this->allowedMimes),
            ]);
        }
    }
}
