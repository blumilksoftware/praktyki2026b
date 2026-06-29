<?php

declare(strict_types=1);

namespace App\DTO\Company;

use Illuminate\Http\UploadedFile;

readonly class UpdateCompanyProfileData
{
    public function __construct(
        public ?UploadedFile $logo,
        public ?string $description,
        public ?array $tags,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            logo: $data["logo"] ?? null,
            description: $data["description"] ?? null,
            tags: $data["tags"] ?? null,
        );
    }
}
