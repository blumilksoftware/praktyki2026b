<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class GetStudentApplicationsAction
{
    /**
     * @return Collection<int, array{
     *     id: string,
     *     offer_id: string,
     *     offer_title: string,
     *     company_name: string,
     *     date_applied: string,
     *     status: string
     * }>
     */
    public function execute(User $student): Collection
    {
        return $student->applications()
            ->with([
                "offer" => fn(BelongsTo $query): BelongsTo => $query->withTrashed()->with("company"),
            ])
            ->orderBy("created_at", "desc")
            ->get()
            ->map(fn(Application $app): array => [
                "id" => $app->id,
                "offer_id" => $app->offer_id,
                "offer_title" => $app->offer?->title ?? "",
                "company_name" => $app->offer?->company?->name ?? "",
                "date_applied" => $app->created_at->toIso8601String(),
                "status" => $app->status->value,
            ]);
    }
}
