<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\University;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Support\Collection;

class GetStudentsBreakdownByField
{
    public function __construct(
        private readonly PaginateStudentsBreakdown $paginateStudentsBreakdown,
    ) {}

    public function execute(
        University $university,
        Collection $students,
        int $perPage = 10,
        int $page = 1,
        string $pageName = "fieldPage",
        ?string $search = null,
        string $sortBy = "fieldName",
        string $sortDirection = "asc",
    ): LengthAwarePaginatorContract {
        $groups = $students->groupBy("study_field_id");

        $rows = $university->studyFields()
            ->orderBy("study_fields.name")
            ->pluck("study_fields.name", "study_fields.id")
            ->union($groups->map(static fn(Collection $group): ?string => $group->first()->studyField?->name))
            ->map(static function (?string $name, string $fieldId) use ($groups): array {
                $group = $groups->get($fieldId, new Collection());

                return [
                    "fieldId" => $fieldId === "" ? null : $fieldId,
                    "fieldName" => $name,
                    "linkedStudents" => $group->count(),
                    "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                    "acceptedPlacements" => $group->sum("accepted_placements_count"),
                ];
            })
            ->values();

        return $this->paginateStudentsBreakdown->execute(
            rows: $rows,
            nameColumn: "fieldName",
            perPage: $perPage,
            page: $page,
            pageName: $pageName,
            search: $search,
            sortBy: $sortBy,
            sortDirection: $sortDirection,
        );
    }
}
