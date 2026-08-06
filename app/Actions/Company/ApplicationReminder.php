<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\ApplicationStatus;
use App\Jobs\SendApplicationReminderJob;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationReminder
{
    public function execute(array $thresholds = [
        14 => "reminder_14_sent_at",
        28 => "reminder_28_sent_at",
    ]): void
    {
        foreach ($thresholds as $days => $column) {
            $this->dispatchReminders($days, $column);
        }
    }

    private function dispatchReminders(int $days, string $column): void
    {
        Application::query()
            ->where("status", ApplicationStatus::Pending)
            ->where("created_at", "<=", now()->subDays($days))
            ->whereNull($column)
            ->each(function (Application $application) use ($days, $column): void {
                DB::transaction(function () use ($application, $days, $column): void {
                    $application->update([$column => now()]);
                    SendApplicationReminderJob::dispatch($application, $days);
                });
            });
    }
}
