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
        public string $address,
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
            address: $data["address"],
            phone: $data["phone"],
            website: $data["website"] ?? null,
        );
    }
}
