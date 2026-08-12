<?php

declare(strict_types=1);

namespace App\Actions\Company;

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
    public function execute(Company $company, University $university): void
    {
        $partnership = $company->partnerships()->where("university_id", $university->id)->first();

        if ($partnership === null) {
            return;
        }

        $partnership->delete();

        $url = route("university.companies.index");

        if ($partnership->status === PartnershipStatus::Active) {
            Notification::send($university->users, new PartnershipEndedNotification(
                enderName: $company->name,
                url: $url,
            ));

            return;
        }

        if ($partnership->requested_by === PartnershipInitiator::Company) {
            Notification::send($university->users, new PartnershipCancelledNotification(
                cancellerName: $company->name,
                url: $url,
            ));
        } else {
            Notification::send($university->users, new PartnershipDeclinedNotification(
                declinerName: $company->name,
                url: $url,
            ));
        }
    }
}
