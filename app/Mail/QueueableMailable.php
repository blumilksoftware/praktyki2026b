<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class QueueableMailable extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function failed(Throwable $exception): void
    {
        $action = $this->getLogAction();

        activity()->causedByAnonymous()
            ->withProperties(array_merge([
                "action" => $action,
                "exception_message" => $exception->getMessage(),
            ], $this->getLogProperties()))
            ->log("failed_to_" . $action);
    }

    abstract protected function getLogAction(): string;

    abstract protected function getLogProperties(): array;
}
