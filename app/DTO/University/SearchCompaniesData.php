<?php

declare(strict_types=1);

namespace App\DTO\University;

readonly class SearchCompaniesData
{
    public function __construct(
        public ?string $name,
        public ?string $city,
        public ?string $tag,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data["name"] ?? null,
            city: $data["city"] ?? null,
            tag: $data["tag"] ?? null,
            perPage: (int)($data["per_page"] ?? 15),
        );
    }
}
