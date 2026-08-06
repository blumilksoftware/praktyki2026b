<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Company\ApplicationReminder;
use Illuminate\Console\Command;

class SendApplicationRemindersCommand extends Command
{
    protected $signature = "send-application-reminders";
    protected $description = "Send 14 and 28 day reminder emails for pending applications";

    public function handle(ApplicationReminder $reminder): void
    {
        $reminder->execute();

        $this->info("Reminder jobs dispatched successfully.");
    }
}
