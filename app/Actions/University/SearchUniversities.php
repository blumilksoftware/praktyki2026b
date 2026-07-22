<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;

class SearchUniversities
{
    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function execute(string $query): array
    {
        return University::query()
            ->verified()
            ->matchingName($query)
            ->orderBy("name")
            ->limit(20)
            ->get(["id", "name"])
            ->map(fn(University $university): array => [
                "id" => $university->id,
                "name" => $university->name,
            ])
            ->all();
    }
}
