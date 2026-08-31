<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Company;
use App\Models\Offer;
use App\Traits\SearchesCaseInsensitively;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchOffers
{
    use SearchesCaseInsensitively;

    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Offer::query()->with("company:id,name");

        if ($filters["status"] !== "all") {
            $query->where("status", $filters["status"]);
        }

        if ($filters["search"] !== "") {
            $this->applyCaseInsensitiveSearch($query, $filters["search"], ["title", "company.name"]);
        }

        if ($filters["sort_key"] === "company") {
            $query->orderBy(
                Company::query()->select("name")->whereColumn("companies.id", "offers.company_id"),
                $filters["sort_dir"],
            );
        } else {
            $query->orderBy($filters["sort_key"], $filters["sort_dir"]);
        }

        return $query->orderByDesc("id")->paginate(20)->appends($filters);
    }
}
