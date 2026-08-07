<?php

declare(strict_types=1);

namespace App\Actions\University;

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

class AddPartnerAction
{
    public function execute(University $university, Company $company): void
    {
        try {
            $university->partnerships()->create([
                "company_id" => $company->id,
                "status" => PartnershipStatus::Pending,
                "requested_by" => PartnershipInitiator::University,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                "company" => __("validation.already_partner"),
            ]);
        }

        Mail::to($company->email)->send(new PartnershipRequestedMail(
            proposerName: $university->name,
            dashboardUrl: route("company.universities.index"),
        ));

        Notification::send($company->users, new PartnershipRequestedNotification(
            proposerName: $university->name,
            url: route("company.universities.index"),
        ));
    }
}
