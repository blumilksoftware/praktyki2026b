<?php

declare(strict_types=1);

namespace App\Listeners\Offer;

use App\Enums\ApplicationStatus;
use App\Events\Offer\OfferBecameUnavailable;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use App\Notifications\OfferUnavailableNotification;
use Illuminate\Support\Facades\Mail;

class SendOfferUnavailableNotification
{
    public function handle(OfferBecameUnavailable $event): void
    {
        $event->offer->applications()
            ->where("status", "!=", ApplicationStatus::Accepted)
            ->with("student")
            ->get()
            ->each(function (Application $application) use ($event): void {
                Mail::to($application->student->email)->queue(
                    new OfferUnavailableMail($event->offer->title, $event->offer->company->name, $event->reason),
                );

                $application->student->notify(new OfferUnavailableNotification($event->offer, $event->reason));
            });
    }
}
