<?php

declare(strict_types=1);

namespace App\DTO\Company;

use App\Enums\PartnershipStatusFilter;

readonly class SearchUniversitiesData
{
    public function __construct(
        public ?string $name,
        public ?string $city,
        public ?PartnershipStatusFilter $partnershipStatus,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data["name"] ?? null,
            city: $data["city"] ?? null,
            partnershipStatus: PartnershipStatusFilter::tryFrom($data["status"] ?? ""),
            perPage: (int)($data["per_page"] ?? 15),
        );
    }
}
