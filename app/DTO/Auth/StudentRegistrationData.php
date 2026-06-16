<?php

declare(strict_types=1);

namespace App\DTO\Auth;

class StudentRegistrationData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $university = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data["first_name"],
            lastName: $data["last_name"],
            email: $data["email"],
            password: $data["password"],
            university: $data["university"] ?? null,
        );
    }
}
