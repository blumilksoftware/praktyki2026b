<?php

declare(strict_types=1);

namespace App\DTO\Offer;

use App\Enums\WorkMode;

readonly class SearchOffersData
{
    /**
     * @param array<int, string> $studyFieldIds
     */
    public function __construct(
        public array $studyFieldIds,
        public ?WorkMode $workMode,
        public ?string $city,
        public ?string $dateFrom,
        public ?string $dateTo,
        public int $dateFlexDays,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            studyFieldIds: $data["study_field_ids"] ?? [],
            workMode: $data["work_mode"] ?? null,
            city: $data["city"] ?? null,
            dateFrom: $data["date_from"] ?? null,
            dateTo: $data["date_to"] ?? null,
            dateFlexDays: $data["date_flex_days"] ?? 0,
            perPage: $data["per_page"] ?? 15,
        );
    }
}
