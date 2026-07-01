<?php

declare(strict_types=1);

namespace App\DTO\Onboarding;

readonly class ProfileStep
{
    public function __construct(
        public string $key,
        public bool $completed,
    ) {}
}
