<?php

declare(strict_types=1);

namespace App\DTO\Organization;

readonly class TeamFiltersData
{
    public function __construct(
        public string $memberSearch,
        public string $invitationSearch,
        public int $memberPage,
        public int $invitationPage,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            memberSearch: $data["member_search"],
            invitationSearch: $data["invitation_search"],
            memberPage: $data["member_page"],
            invitationPage: $data["invitation_page"],
            perPage: $data["per_page"] ?? 10,
        );
    }
}
