<?php

declare(strict_types=1);

namespace App\Listeners\Offer;

use App\Events\Offer\OfferBecameUnavailable;
use App\Mail\Offer\OfferUnavailableMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class SendOfferUnavailableNotification
{
    public function handle(OfferBecameUnavailable $event): void
    {
        $event->offer->applications()->with("student")->get()->each(
            fn(Application $application): mixed => Mail::to($application->student->email)->queue(
                new OfferUnavailableMail($event->offer->title, $event->offer->company->name, $event->reason),
            ),
        );
    }
}
