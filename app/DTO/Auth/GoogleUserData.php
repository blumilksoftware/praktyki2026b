<?php

declare(strict_types=1);

namespace App\DTO\Auth;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleUserData
{
    public function __construct(
        public readonly string $googleId,
        public readonly string $email,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
    ) {}

    public static function fromSocialite(SocialiteUser $user): self
    {
        return new self(
            googleId: $user->getId(),
            email: $user->getEmail(),
            firstName: $user->offsetGet("given_name"),
            lastName: $user->offsetGet("family_name"),
        );
    }
}
