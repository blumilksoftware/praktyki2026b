<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Student = "student";
    case SuperAdmin = "superAdmin";
    case UniversityAdmin = "universityAdmin";
    case CompanyAdmin = "companyAdmin";
}
