<?php

declare(strict_types=1);

namespace App\DTO\Auth;

readonly class UniversityRegistrationData
{
    public function __construct(
        public string $universityName,
        public string $email,
        public string $domain,
        public string $password,
        public string $street,
        public string $buildingNumber,
        public string $postalCode,
        public string $city,
        public string $phone,
        public ?string $website = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            universityName: $data["university_name"],
            email: $data["email"],
            domain: $data["domain"],
            password: $data["password"],
            street: $data["street"],
            buildingNumber: $data["building_number"],
            postalCode: $data["postal_code"],
            city: $data["city"],
            phone: $data["phone"],
            website: $data["website"] ?? null,
        );
    }
}
