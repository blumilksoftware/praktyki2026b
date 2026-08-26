<?php

declare(strict_types=1);

namespace App\Actions\Company;

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
use App\Models\OrganizationInvitation;
use Carbon\Carbon;

class GetCompanyDashboardStats
{
    public function execute(Company $company): array
    {
        return [
            ...$this->offerStats($company),
            ...$this->applicationStats($company),
            ...$this->teamStats($company),
            ...$this->universityStats($company),
        ];
    }

    private function offerStats(Company $company): array
    {
        $closingSoonThreshold = Carbon::today()->addWeek();

        $stats = $company->offers()
            ->selectRaw(
                "count(*) as total, " .
                "sum(case when status = ? then 1 else 0 end) as published, " .
                "sum(case when status = ? then 1 else 0 end) as draft, " .
                "sum(case when status = ? and end_date >= ? and end_date <= ? then 1 else 0 end) as closing_soon, " .
                "sum(spots) as total_spots, " .
                "sum(spots) - sum((select count(*) from applications " .
                "where applications.offer_id = offers.id and applications.status = ?)) as remaining_spots",
                [
                    OfferStatus::Published->value,
                    OfferStatus::Draft->value,
                    OfferStatus::Published->value,
                    Carbon::today(),
                    $closingSoonThreshold,
                    ApplicationStatus::Accepted->value,
                ],
            )
            ->first();

        return [
            "total_offers" => (int) $stats->total,
            "published_offers" => (int) $stats->published,
            "draft_offers" => (int) $stats->draft,
            "offers_closing_soon" => (int) $stats->closing_soon,
            "total_spots" => (int) $stats->total_spots,
            "remaining_spots" => (int) $stats->remaining_spots,
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
            "applications_count" => (int) $stats->total,
            "pending_applications_count" => (int) $stats->pending,
            "accepted_applications_count" => (int) $stats->accepted,
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
            "team_size" => $teamSize,
            "pending_invitations_count" => (int) $invitationStats->pending,
            "accepted_invitations_count" => (int) $invitationStats->accepted,
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
            "university_partnerships_count" => (int) $stats->active,
            "open_partnership_requests_count" => (int) $stats->open_requests,
        ];
    }
}
