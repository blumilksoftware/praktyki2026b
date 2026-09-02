<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\VerificationStatus;
use App\Models\Company;
use App\Models\IndustryTag;
use Illuminate\Support\Str;

class GetCompanyFilterOptions
{
    public function execute(): array
    {
        $companies = Company::query()
            ->where("verification_status", VerificationStatus::Verified)
            ->get(["city"]);

        return [
            "cities" => $companies->pluck("city")->filter()->unique()->sortBy(fn(string $city): string => Str::ascii($city))->values()->all(),
            "tags" => IndustryTag::query()->pluck("name")->sortBy(fn(string $tag): string => Str::ascii($tag))->values()->all(),
        ];
    }
}
