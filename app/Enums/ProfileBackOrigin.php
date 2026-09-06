<?php

declare(strict_types=1);

namespace App\Enums;

enum ProfileBackOrigin: string
{
    case AdminApplications = "admin.applications";
    case UniversityCompanies = "university.companies";
    case CompanyUniversities = "company.universities";
    case StudentDashboard = "student.dashboard";
    case Offers = "offers";

    public function routeName(): string
    {
        return match ($this) {
            self::AdminApplications => "admin.applications.index",
            self::UniversityCompanies => "university.companies.index",
            self::CompanyUniversities => "company.universities.index",
            self::StudentDashboard => "student.dashboard",
            self::Offers => "offers.index",
        };
    }
}
