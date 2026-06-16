<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = "student";
    case SuperAdmin = "super_admin";
    case UniversityAdmin = "university_admin";
    case CompanyAdmin = "company_admin";
}
