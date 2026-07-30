<?php

declare(strict_types=1);

namespace App\Actions\University;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetStudentsBreakdownByField
{
    public function execute(
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "fieldPage",
    ): LengthAwarePaginatorContract {
        $grouped = $students
            ->groupBy("study_field")
            ->map(static fn(Collection $group) => [
                "fieldId" => $group->first()->study_field,
                "fieldName" => $group->first()->studyField?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => (int)$group->sum("applications_submitted_count"),
                "acceptedPlacements" => (int)$group->sum("accepted_placements_count"),
            ])
            ->values();

        $total = $grouped->count();
        $slice = $grouped->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            items: $slice,
            total: $total,
            perPage: $perPage,
            currentPage: $page,
            options: [
                "path" => LengthAwarePaginator::resolveCurrentPath(),
                "pageName" => $pageName,
            ],
        );
    }
}
