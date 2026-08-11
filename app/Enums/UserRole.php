<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = "student";
    case SuperAdmin = "superAdmin";
    case UniversityAdmin = "universityAdmin";
    case UniversityMember = "universityMember";
    case CompanyAdmin = "companyAdmin";
    case CompanyMember = "companyMember";

    public function organizationType(): ?OrganizationType
    {
        return match ($this) {
            self::CompanyAdmin, self::CompanyMember => OrganizationType::Company,
            self::UniversityAdmin, self::UniversityMember => OrganizationType::University,
            default => null,
        };
    }
}
