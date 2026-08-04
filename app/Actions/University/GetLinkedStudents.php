<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\UserRole;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;

class GetLinkedStudents
{
    public function execute(University $university, ?Carbon $from = null, ?Carbon $to = null): EloquentCollection
    {
        $dateFilter = static function ($query) use ($from, $to): void {
            if ($from) {
                $query->where("created_at", ">=", $from);
            }

            if ($to) {
                $query->where("created_at", "<=", $to);
            }
        };

        return User::query()
            ->where("role", UserRole::Student)
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
    }
}
