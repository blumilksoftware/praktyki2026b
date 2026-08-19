<?php

declare(strict_types=1);

namespace App\DTO\University;

use Illuminate\Http\UploadedFile;

readonly class UpdateUniversityProfileData
{
    public function __construct(
        public ?string $domain,
        public ?UploadedFile $logo,
        public ?string $description,
        public ?string $externalFormUrl,
        public ?string $website,
        public ?string $phone,
        public ?string $street,
        public ?string $postalCode,
        public ?string $city,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            domain: $data["domain"] ?? null,
            logo: $data["logo"] ?? null,
            description: $data["description"] ?? null,
            externalFormUrl: $data["external_form_url"] ?? null,
            website: $data["website"] ?? null,
            phone: $data["phone"] ?? null,
            street: $data["street"] ?? null,
            postalCode: $data["postal_code"] ?? null,
            city: $data["city"] ?? null,
        );
    }
}
