<?php

declare(strict_types=1);

namespace App\DTO\Company;

readonly class SearchUniversitiesData
{

    public function __construct(
        public ?string $name,
        public ?string $city,
        public ?string $partnershipStatus,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data["name"] ?? null,
            city: $data["city"] ?? null,
            partnershipStatus: $data["status"] ?? null,
            perPage: (int)($data["per_page"] ?? 15),
        );
    }
}
