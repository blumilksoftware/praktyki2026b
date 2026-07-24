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
        public ?string $website,
        public ?string $phone,
        public ?string $street,
        public ?string $postal_code,
        public ?string $city,
        public ?string $nip,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            logo: $data["logo"] ?? null,
            description: $data["description"] ?? null,
            tags: $data["tags"] ?? null,
            website: $data["website"] ?? null,
            phone: $data["phone"] ?? null,
            street: $data["street"] ?? null,
            postal_code: $data["postal_code"] ?? null,
            city: $data["city"] ?? null,
            nip: $data["nip"] ?? null,
        );
    }
}
