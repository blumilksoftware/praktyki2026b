<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Mail\JobApplication\JobApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\Offer;
use App\Notifications\ApplicationStatusChangedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UpdateApplicationStatusAction
{
    public function execute(Application $application, ApplicationStatus $status): Application
    {
        $offerToClose = DB::transaction(function () use ($application, $status): ?Offer {
            if ($status !== ApplicationStatus::Accepted) {
                $application->update(["status" => $status]);

                return null;
            }

            $offer = Offer::whereKey($application->offer_id)->lockForUpdate()->firstOrFail();
            $remainingSpots = $offer->remainingSpots();

            if ($remainingSpots <= 0) {
                throw ValidationException::withMessages([
                    "status" => __("validation.offer_spots_exhausted"),
                ]);
            }

            $application->update(["status" => $status]);

            return $remainingSpots === 1 && $offer->status === OfferStatus::Published ? $offer : null;
        });

        $offerToClose?->update(["status" => OfferStatus::Closed]);

        $application->loadMissing(["offer.company", "student"]);

        $statusTranslated = __("emails.job_application.status.{$status->value}");

        Mail::to($application->student->email)->send(
            new JobApplicationStatusChangedMail(
                jobTitle: $application->offer->title,
                companyName: $application->offer->company->name,
                status: $statusTranslated,
                dashboardUrl: url("/"),
            ),
        );

        $application->student->notify(new ApplicationStatusChangedNotification($application));

        return $application;
    }
}
