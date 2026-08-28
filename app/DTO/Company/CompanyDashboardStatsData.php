<?php

declare(strict_types=1);

namespace App\DTO\Company;

readonly class CompanyDashboardStatsData
{
    public function __construct(
        public int $totalOffers,
        public int $publishedOffers,
        public int $draftOffers,
        public int $offersClosingSoon,
        public int $totalSpots,
        public int $remainingSpots,
        public int $applicationsCount,
        public int $pendingApplicationsCount,
        public int $acceptedApplicationsCount,
        public int $teamSize,
        public int $pendingInvitationsCount,
        public int $acceptedInvitationsCount,
        public int $universityPartnershipsCount,
        public int $openPartnershipRequestsCount,
    ) {}
}
