<?php

declare(strict_types=1);

namespace App\DTO\University;

use Illuminate\Http\UploadedFile;

readonly class UpdateUniversityProfileData
{
    public function __construct(
        public ?string $domain,
        public ?UploadedFile $logo,
        public ?string $externalFormUrl,
        public ?array $faculties,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data["domain"] ?? null,
            logo: $data["logo"] ?? null,
            externalFormUrl: $data["external_form_url"] ?? null,
            faculties: $data["faculties"] ?? null,
        );
    }
}
