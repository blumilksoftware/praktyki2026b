<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Company\CompanyDashboardStatsData;
use App\Enums\ApplicationStatus;
use App\Enums\InvitationStatus;
use App\Enums\OfferStatus;
use App\Enums\OrganizationType;
use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\OrganizationInvitation;
use Carbon\Carbon;

class GetCompanyDashboardStats
{
    public function execute(Company $company): CompanyDashboardStatsData
    {
        $applicationStats = $this->applicationStats($company);
        $offerStats = $this->offerStats($company, $applicationStats);
        $teamStats = $this->teamStats($company);
        $universityStats = $this->universityStats($company);

        return new CompanyDashboardStatsData(
            ...$offerStats,
            ...$applicationStats,
            ...$teamStats,
            ...$universityStats,
        );
    }

    private function offerStats(Company $company, array $applicationStats): array
    {
        $stats = $company->offers()
            ->selectRaw(
                "count(*) as total, " .
                "sum(case when status = ? then 1 else 0 end) as published, " .
                "sum(case when status = ? then 1 else 0 end) as draft, " .
                "sum(case when status = ? and end_date >= ? and end_date <= ? then 1 else 0 end) as closing_soon, " .
                "sum(spots) as total_spots",
                [
                    OfferStatus::Published->value,
                    OfferStatus::Draft->value,
                    OfferStatus::Published->value,
                    Carbon::today(),
                    Offer::closingSoonThreshold(),
                ],
            )
            ->first();

        return [
            "totalOffers" => (int)$stats->total,
            "publishedOffers" => (int)$stats->published,
            "draftOffers" => (int)$stats->draft,
            "offersClosingSoon" => (int)$stats->closing_soon,
            "totalSpots" => (int)$stats->total_spots,
            "remainingSpots" => (int)$stats->total_spots - $applicationStats["acceptedApplicationsCount"],
        ];
    }

    private function applicationStats(Company $company): array
    {
        $stats = Application::query()
            ->join("offers", "offers.id", "=", "applications.offer_id")
            ->where("offers.company_id", $company->id)
            ->selectRaw(
                "count(*) as total, " .
                "sum(case when applications.status = ? then 1 else 0 end) as pending, " .
                "sum(case when applications.status = ? then 1 else 0 end) as accepted",
                [ApplicationStatus::Pending->value, ApplicationStatus::Accepted->value],
            )
            ->first();

        return [
            "applicationsCount" => (int)$stats->total,
            "pendingApplicationsCount" => (int)$stats->pending,
            "acceptedApplicationsCount" => (int)$stats->accepted,
        ];
    }

    private function teamStats(Company $company): array
    {
        $teamSize = $company->users()
            ->whereIn("role", [UserRole::CompanyAdmin, UserRole::CompanyMember])
            ->where("status", "!=", UserStatus::Deleted)
            ->count();

        $invitationStats = OrganizationInvitation::query()
            ->where("organization_id", $company->id)
            ->where("organization_type", OrganizationType::Company)
            ->selectRaw(
                "sum(case when status = ? then 1 else 0 end) as pending, " .
                "sum(case when status = ? then 1 else 0 end) as accepted",
                [InvitationStatus::Pending->value, InvitationStatus::Accepted->value],
            )
            ->first();

        return [
            "teamSize" => $teamSize,
            "pendingInvitationsCount" => (int)$invitationStats->pending,
            "acceptedInvitationsCount" => (int)$invitationStats->accepted,
        ];
    }

    private function universityStats(Company $company): array
    {
        $stats = $company->partnerships()
            ->selectRaw(
                "sum(case when status = ? then 1 else 0 end) as active, " .
                "sum(case when status = ? and requested_by = ? then 1 else 0 end) as open_requests",
                [
                    PartnershipStatus::Active->value,
                    PartnershipStatus::Pending->value,
                    PartnershipInitiator::University->value,
                ],
            )
            ->first();

        return [
            "universityPartnershipsCount" => (int)$stats->active,
            "openPartnershipRequestsCount" => (int)$stats->open_requests,
        ];
    }
}
