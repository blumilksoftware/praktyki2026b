<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\DTO\University\SearchCompaniesData;
use App\Enums\VerificationStatus;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchCompanies
{
    public function execute(SearchCompaniesData $data, string $universityId): LengthAwarePaginator
    {
        $query = Company::query()
            ->where("verification_status", VerificationStatus::Verified);

        if ($data->name !== null) {
            $query->whereRaw("LOWER(name) LIKE ?", ["%" . strtolower($data->name) . "%"]);
        }

        if ($data->city !== null) {
            $query->whereRaw("LOWER(city) LIKE ?", ["%" . strtolower($data->city) . "%"]);
        }

        if ($data->tag !== null) {
            $tagElementsExpression = $query->getModel()->getConnection()->getDriverName() === "sqlite"
                ? "SELECT 1 FROM json_each(tags) WHERE LOWER(value) = ?"
                : "SELECT 1 FROM json_array_elements_text(tags) AS tag WHERE LOWER(tag) = ?";

            $query->whereRaw("EXISTS ({$tagElementsExpression})", [strtolower($data->tag)]);
        }

        return $query
            ->withCount(["offers" => fn($q) => $q->published()])
            ->with(["partnerships" => fn($q) => $q->where("university_id", $universityId)])
            ->orderBy("name")
            ->paginate($data->perPage)
            ->withQueryString()
            ->through(function (Company $company) {
                $partnership = $company->partnerships->first();

                return [
                    "id" => $company->id,
                    "name" => $company->name,
                    "email" => $company->email,
                    "street" => $company->street,
                    "postal_code" => $company->postal_code,
                    "city" => $company->city,
                    "phone" => $company->phone,
                    "website" => $company->website,
                    "logo_path" => $company->logo_path,
                    "description" => $company->description,
                    "tags" => $company->tags ?? [],
                    "active_offers_count" => $company->offers_count,
                    "partnership_status" => $partnership ? $partnership->status->value : "none",
                ];
            });
    }
}
