<?php

declare(strict_types=1);

namespace App\DTO\Student;

readonly class UpdateStudentProfileData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?int $age,
        public ?string $street,
        public ?string $postalCode,
        public ?string $city,
        public ?string $university,
        public ?string $studyField,
        public ?int $studyYear,
        public ?string $specialization,
        public array $studyFieldIds,
        public array $preferredCities,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data["first_name"],
            lastName: $data["last_name"],
            age: $data["age"] ?? null,
            street: $data["street"] ?? null,
            postalCode: $data["postal_code"] ?? null,
            city: $data["city"] ?? null,
            university: $data["university"] ?? null,
            studyField: $data["study_field"] ?? null,
            studyYear: $data["study_year"] ?? null,
            specialization: $data["specialization"] ?? null,
            studyFieldIds: $data["study_field_ids"] ?? [],
            preferredCities: $data["preferred_cities"] ?? [],
        );
    }
}
