<?php

declare(strict_types=1);

namespace App\Actions\University;

use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Models\Company;
use App\Models\University;
use App\Notifications\PartnershipCancelledNotification;
use App\Notifications\PartnershipDeclinedNotification;
use App\Notifications\PartnershipEndedNotification;
use Illuminate\Support\Facades\Notification;

class RemovePartnerAction
{
    public function execute(University $university, Company $company): void
    {
        $partnership = $university->partnerships()->where("company_id", $company->id)->first();

        if ($partnership === null) {
            return;
        }

        $partnership->delete();

        $url = route("company.universities.index");

        if ($partnership->status === PartnershipStatus::Active) {
            Notification::send($company->users, new PartnershipEndedNotification(
                enderName: $university->name,
                url: $url,
            ));

            return;
        }

        if ($partnership->requested_by === PartnershipInitiator::University) {
            Notification::send($company->users, new PartnershipCancelledNotification(
                cancellerName: $university->name,
                url: $url,
            ));
        } else {
            Notification::send($company->users, new PartnershipDeclinedNotification(
                declinerName: $university->name,
                url: $url,
            ));
        }
    }
}
