<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\VerificationStatus;
use App\Models\University;
use Illuminate\Support\Str;

class GetUniversityFilterOptions
{
    public function execute(): array
    {
        $universities = University::query()
            ->where("verification_status", VerificationStatus::Verified)
            ->get(["city"]);

        return [
            "cities" => $universities->pluck("city")->filter()->unique()->sortBy(fn(string $city): string => Str::ascii($city))->values()->all(),
        ];
    }
}
