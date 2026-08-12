<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Company;
use App\Models\University;

enum OrganizationType: string
{
    case Company = "company";
    case University = "university";

    public static function forOrganization(Company|University $organization): self
    {
        return $organization instanceof Company ? self::Company : self::University;
    }

    public function adminRole(): UserRole
    {
        return match ($this) {
            self::Company => UserRole::CompanyAdmin,
            self::University => UserRole::UniversityAdmin,
        };
    }

    public function memberRole(): UserRole
    {
        return match ($this) {
            self::Company => UserRole::CompanyMember,
            self::University => UserRole::UniversityMember,
        };
    }
}
