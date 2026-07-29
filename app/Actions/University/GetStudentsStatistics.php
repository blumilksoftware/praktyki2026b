<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\UserRole;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GetStudentsStatistics
{
    public function execute(University $university, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $dateFilter = function ($query) use ($from, $to): void {
            if ($from) {
                $query->where("created_at", ">=", $from);
            }

            if ($to) {
                $query->where("created_at", "<=", $to);
            }
        };

        $linkedStudents = User::where("role", UserRole::Student)
            ->where(function ($query) use ($university): void {
                $query->where("email", "like", "%@" . $university->domain)
                    ->orWhere("organization_id", $university->id);
            })
            ->with("studyField.faculty")
            ->withCount([
                "applications as applications_submitted_count" => $dateFilter,
                "applications as accepted_placements_count" => function ($query) use ($dateFilter): void {
                    $dateFilter($query);
                    $query->where("status", "accepted");
                },
            ])
            ->get();

        $breakdownByFaculty = $linkedStudents
            ->groupBy(fn(User $student) => $student->studyField?->faculty_id ?? "unknown")
            ->map(fn(Collection $group) => [
                "facultyId" => $group->first()->studyField?->faculty_id,
                "facultyName" => $group->first()->studyField?->faculty?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                "acceptedPlacements" => $group->sum("accepted_placements_count"),
            ])
            ->values();

        $breakdownByField = $linkedStudents
            ->groupBy("study_field")
            ->map(fn(Collection $group) => [
                "fieldId" => $group->first()->study_field,
                "fieldName" => $group->first()->studyField?->name,
                "linkedStudents" => $group->count(),
                "applicationsSubmitted" => $group->sum("applications_submitted_count"),
                "acceptedPlacements" => $group->sum("accepted_placements_count"),
            ])
            ->values();

        return [
            "linkedStudents" => $linkedStudents->count(),
            "applicationsSubmitted" => $linkedStudents->sum("applications_submitted_count"),
            "acceptedPlacements" => $linkedStudents->sum("accepted_placements_count"),
            "breakdownByFaculty" => $breakdownByFaculty,
            "breakdownByField" => $breakdownByField,
        ];
    }
}
