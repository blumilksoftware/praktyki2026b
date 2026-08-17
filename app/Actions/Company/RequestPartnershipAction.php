<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\PartnershipInitiator;
use App\Enums\PartnershipStatus;
use App\Mail\Partnership\PartnershipRequestedMail;
use App\Models\Company;
use App\Models\University;
use App\Notifications\PartnershipRequestedNotification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class RequestPartnershipAction
{
    public function execute(Company $company, University $university): void
    {
        try {
            $company->partnerships()->create([
                "university_id" => $university->id,
                "status" => PartnershipStatus::Pending,
                "requested_by" => PartnershipInitiator::Company,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                "university" => __("validation.already_university_partner"),
            ]);
        }

        Mail::to($university->email)->send(new PartnershipRequestedMail(
            proposerName: $company->name,
            dashboardUrl: route("university.companies.index"),
        ));

        Notification::send($university->users, new PartnershipRequestedNotification(
            proposerName: $company->name,
            url: route("university.companies.index"),
        ));
    }
}
