<?php

declare(strict_types=1);

namespace App\DTO\Auth;

readonly class CompanyRegistrationData
{
    public function __construct(
        public string $companyName,
        public string $nip,
        public string $email,
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
            companyName: $data["company_name"],
            nip: $data["nip"],
            email: $data["email"],
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
