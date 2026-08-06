<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Company;

use App\Actions\Company\ApplicationReminder;
use App\Enums\ApplicationStatus;
use App\Jobs\SendApplicationReminderJob;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApplicationReminderTest extends TestCase
{
    use RefreshDatabase;

    public function testItDispatchesJobsFor14And28DaysAndUpdatesColumns(): void
    {
        Queue::fake();

        $app14 = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => null,
        ]);

        $app28 = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(28),
            "reminder_14_sent_at" => now()->subDays(14),
            "reminder_28_sent_at" => null,
        ]);

        $appIgnore = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(5),
        ]);

        $action = new ApplicationReminder();
        $action->execute();

        Queue::assertPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $app14->id && $job->days === 14);

        Queue::assertPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $app28->id && $job->days === 28);

        Queue::assertNotPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $appIgnore->id);

        $this->assertNotNull($app14->refresh()->reminder_14_sent_at);
        $this->assertNotNull($app28->refresh()->reminder_28_sent_at);
    }

    public function testItDoesNotDispatchForNonPendingStatus(): void
    {
        Queue::fake();

        Application::factory()->create([
            "status" => ApplicationStatus::Accepted,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => null,
        ]);

        (new ApplicationReminder())->execute();

        Queue::assertNothingPushed();
    }

    public function testItDoesNotDispatchIfReminderAlreadySent(): void
    {
        Queue::fake();

        Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => now(),
        ]);

        Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(28),
            "reminder_14_sent_at" => now()->subDays(14),
            "reminder_28_sent_at" => now(),
        ]);

        (new ApplicationReminder())->execute();

        Queue::assertNothingPushed();
    }

    public function testItDoesNotDispatchBeforeThreshold(): void
    {
        Queue::fake();

        $tooEarly14 = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(13),
            "reminder_14_sent_at" => null,
        ]);
        $tooEarly28 = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(27),
            "reminder_14_sent_at" => now()->subDays(13),
            "reminder_28_sent_at" => null,
        ]);

        (new ApplicationReminder())->execute();

        Queue::assertNotPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $tooEarly14->id);
        Queue::assertNotPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $tooEarly28->id && $job->days === 28);
        $this->assertNull($tooEarly14->refresh()->reminder_14_sent_at);
        $this->assertNull($tooEarly28->refresh()->reminder_28_sent_at);
    }

    public function testItDispatchesBothThresholdsIndependentlyForSameApplication(): void
    {
        Queue::fake();
        $app = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(30),
            "reminder_14_sent_at" => null,
            "reminder_28_sent_at" => null,
        ]);

        (new ApplicationReminder())->execute();

        Queue::assertPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $app->id && $job->days === 14);
        Queue::assertPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $app->id && $job->days === 28);
        $this->assertNotNull($app->refresh()->reminder_14_sent_at);
        $this->assertNotNull($app->refresh()->reminder_28_sent_at);
    }

    public function testItDoesNothingWhenNoApplicationsExist(): void
    {
        Queue::fake();

        (new ApplicationReminder())->execute();

        Queue::assertNothingPushed();
    }

    public function testItDoesNothingWhenEmptyThresholdsPassed(): void
    {
        Queue::fake();

        Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(14),
        ]);

        (new ApplicationReminder())->execute([]);

        Queue::assertNothingPushed();
    }

    public function testItOnlyDispatchesForMatchingApplicationsAmongMixedData(): void
    {
        Queue::fake();

        $due = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => null,
        ]);
        $wrongStatus = Application::factory()->create([
            "status" => ApplicationStatus::Accepted,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => null,
        ]);
        $notDueYet = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(1),
            "reminder_14_sent_at" => null,
        ]);
        $alreadySent = Application::factory()->create([
            "status" => ApplicationStatus::Pending,
            "created_at" => now()->subDays(14),
            "reminder_14_sent_at" => now(),
        ]);

        (new ApplicationReminder())->execute();

        Queue::assertPushed(SendApplicationReminderJob::class, 1);
        Queue::assertPushed(SendApplicationReminderJob::class, fn($job) => $job->application->id === $due->id);
    }
}
