<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class GetStudentApplicationsAction
{
    public function execute(User $student): Collection
    {
        return $student->applications()
            ->with([
                "offer" => fn(BelongsTo $query): BelongsTo => $query->withTrashed()->with("company")->withCount("acceptedApplications"),
            ])
            ->orderBy("created_at", "desc")
            ->get()
            ->map(fn(Application $app): array => [
                "id" => $app->id,
                "offer_id" => $app->offer_id,
                "offer_deleted" => $app->offer?->trashed() ?? true,
                "offer_title" => $app->offer?->title ?? "",
                "company_name" => $app->offer?->company?->name ?? "",
                "remaining_spots" => $app->offer?->remainingSpots(),
                "date_applied" => $app->created_at->toIso8601String(),
                "status" => $app->status->value,
            ]);
    }
}
