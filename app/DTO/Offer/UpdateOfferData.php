<?php

declare(strict_types=1);

namespace App\DTO\Offer;

use App\Enums\OfferStatus;
use App\Enums\WorkMode;

readonly class UpdateOfferData
{
    public function __construct(
        public string $title,
        public string $description,
        public int $spots,
        public string $city,
        public string $startDate,
        public string $endDate,
        public WorkMode $workMode,
        public OfferStatus $status,
        public bool $isPaid,
        public ?int $salaryMin,
        public ?int $salaryMax,
        public array $studyFieldIds,
        public array $universityIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data["title"],
            description: $data["description"],
            spots: $data["spots"],
            city: $data["city"],
            startDate: $data["start_date"],
            endDate: $data["end_date"],
            workMode: $data["work_mode"],
            status: $data["status"],
            isPaid: $data["is_paid"],
            salaryMin: $data["salary_min"] ?? null,
            salaryMax: $data["salary_max"] ?? null,
            studyFieldIds: $data["study_field_ids"] ?? [],
            universityIds: $data["university_ids"] ?? [],
        );
    }
}
