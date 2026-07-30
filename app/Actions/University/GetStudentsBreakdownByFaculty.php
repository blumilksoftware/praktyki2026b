<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Models\User;
use Illuminate\Support\Collection;

class GetStudentsBreakdownByFaculty
{
    public function execute(Collection $students): Collection
    {
        return $students
            ->groupBy(static fn(User $student) => $student->studyField?->faculty_id ?? "unknown")
            ->map(static fn(Collection $group) => [
                "facultyId" => $group->first()->studyField?->faculty_id,
                "facultyName" => $group->first()->studyField?->faculty?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => (int)$group->sum("applications_submitted_count"),
                "acceptedPlacements" => (int)$group->sum("accepted_placements_count"),
            ])
            ->values();
    }
}
