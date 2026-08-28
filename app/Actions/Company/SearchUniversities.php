<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTO\Company\SearchUniversitiesData;
use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Enums\PartnershipStatusFilter;
use App\Enums\VerificationStatus;
use App\Models\University;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchUniversities
{
    public function execute(SearchUniversitiesData $data, string $companyId): LengthAwarePaginator
    {
        $query = University::query()
            ->where("verification_status", VerificationStatus::Verified);

        if ($data->name !== null) {
            $query->whereRaw("LOWER(name) LIKE ?", ["%" . mb_strtolower($data->name) . "%"]);
        }

        if ($data->city !== null) {
            $query->whereRaw("LOWER(city) LIKE ?", ["%" . mb_strtolower($data->city) . "%"]);
        }

        if ($data->partnershipStatus !== null) {
            $this->applyPartnershipStatusFilter($query, $data->partnershipStatus, $companyId);
        }

        return $query
            ->with(["partnerships" => fn($q) => $q->where("company_id", $companyId)])
            ->orderBy("name")
            ->paginate($data->perPage)
            ->withQueryString()
            ->through(function (University $university) {
                $partnership = $university->partnerships->first();

                return [
                    "id" => $university->id,
                    "name" => $university->name,
                    "email" => $university->email,
                    "street" => $university->street,
                    "postal_code" => $university->postal_code,
                    "city" => $university->city,
                    "phone" => $university->phone,
                    "website" => $university->website,
                    "logo_path" => $university->logo_path,
                    "partnership_status" => $this->resolveStatus($partnership),
                ];
            });
    }

    /**
     * @param Builder<University> $query
     */
    private function applyPartnershipStatusFilter(Builder $query, PartnershipStatusFilter $partnershipStatus, string $companyId): void
    {
        match ($partnershipStatus) {
            PartnershipStatusFilter::PendingIncoming => $query->whereHas(
                "partnerships",
                fn(Builder $partnershipQuery): Builder => $partnershipQuery
                    ->where("company_id", $companyId)
                    ->where("status", PartnershipStatus::Pending)
                    ->where("requested_by", PartnershipInitiator::University),
            ),
            PartnershipStatusFilter::PendingOutgoing => $query->whereHas(
                "partnerships",
                fn(Builder $partnershipQuery): Builder => $partnershipQuery
                    ->where("company_id", $companyId)
                    ->where("status", PartnershipStatus::Pending)
                    ->where("requested_by", PartnershipInitiator::Company),
            ),
            PartnershipStatusFilter::Active => $query->whereHas(
                "partnerships",
                fn(Builder $partnershipQuery): Builder => $partnershipQuery
                    ->where("company_id", $companyId)
                    ->where("status", PartnershipStatus::Active),
            ),
            default => null,
        };
    }

    private function resolveStatus(mixed $partnership): string
    {
        if ($partnership === null) {
            return "none";
        }

        if ($partnership->status !== PartnershipStatus::Pending) {
            return $partnership->status->value;
        }

        return $partnership->requested_by === PartnershipInitiator::Company
            ? "pending_outgoing"
            : "pending_incoming";
    }
}
