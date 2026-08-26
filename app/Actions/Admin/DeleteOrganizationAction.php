<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\InvitationStatus;
use App\Enums\OfferStatus;
use App\Enums\OrganizationType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Offer;
use App\Models\OrganizationInvitation;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeleteOrganizationAction
{
    public function execute(Company|University $organization): void
    {
        DB::transaction(function () use ($organization): void {
            $originalName = $organization->name;

            $this->revokeInvitations($organization);

            if ($organization instanceof Company) {
                $this->closeOffersAndRejectApplications($organization);
            }

            $this->processUsers($organization);
            $this->anonymize($organization);

            $organization->save();
            $organization->delete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($organization)
                ->withProperties(["name" => $originalName])
                ->log($organization instanceof Company ? "company_deleted" : "university_deleted");
        });
    }

    private function revokeInvitations(Company|University $organization): void
    {
        $organizationType = match (true) {
            $organization instanceof Company => OrganizationType::Company,
            $organization instanceof University => OrganizationType::University,
        };

        OrganizationInvitation::query()
            ->where("organization_id", $organization->id)
            ->where("organization_type", $organizationType->value)
            ->where("status", InvitationStatus::Pending->value)
            ->update(["status" => InvitationStatus::Revoked->value]);
    }

    private function closeOffersAndRejectApplications(Company $company): void
    {
        $offerIds = $company->offers()->pluck("id");

        if ($offerIds->isEmpty()) {
            return;
        }

        Application::query()
            ->whereIn("offer_id", $offerIds)
            ->whereIn("status", [
                ApplicationStatus::Pending->value,
                ApplicationStatus::Reviewed->value,
            ])
            ->update(["status" => ApplicationStatus::Rejected->value]);

        Offer::query()
            ->whereIn("id", $offerIds)
            ->update(["status" => OfferStatus::Closed->value]);
    }

    private function processUsers(Company|University $organization): void
    {
        if ($organization instanceof University) {
            User::query()
                ->where("organization_id", $organization->id)
                ->where("role", UserRole::Student->value)
                ->update(["organization_id" => null]);
        }

        User::query()
            ->where("organization_id", $organization->id)
            ->delete();
    }

    private function anonymize(Company|University $organization): void
    {
        $data = [
            "name" => sprintf("Deleted organization #%s", $organization->id),
            "email" => sprintf("deleted-%s-%s@example.invalid", $organization->id, Str::random(8)),
            "street" => "",
            "postal_code" => "",
            "city" => "",
            "phone" => "",
            "website" => null,
            "description" => null,
            "logo_path" => null,
            "rejection_reason" => null,
        ];

        if ($organization instanceof Company) {
            $data["nip"] = sprintf("deleted-%s", $organization->id);
            $data["tags"] = null;
        }

        if ($organization instanceof University) {
            $data["domain"] = sprintf("deleted-%s.invalid", $organization->id);
            $data["external_form_url"] = null;
        }

        $organization->fill($data);
    }
}
