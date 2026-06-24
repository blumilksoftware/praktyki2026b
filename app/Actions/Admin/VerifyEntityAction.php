<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Enums\VerificationStatus;
use App\Mail\Verification\CompanyVerificationAcceptMail;
use App\Mail\Verification\CompanyVerificationRejectMail;
use App\Mail\Verification\UniversityVerificationAcceptMail;
use App\Mail\Verification\UniversityVerificationRejectMail;
use App\Models\Company;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class VerifyEntityAction
{
    public function verify(Company|University $entity, User $admin): void
    {
        $entity->update(["verification_status" => VerificationStatus::Verified]);

        $this->sendAcceptMail($entity);

        activity()->causedBy($admin)
            ->performedOn($entity)
            ->withProperties(["action" => "verify"])
            ->log($entity::class === Company::class ? "company_verified" : "university_verified");
    }

    public function reject(Company|University $entity, string $rejectionReason, User $admin): void
    {
        $entity->update(["verification_status" => VerificationStatus::Rejected]);

        $this->sendRejectMail($entity, $rejectionReason);

        activity()->causedBy($admin)
            ->performedOn($entity)
            ->withProperties(["action" => "reject", "rejection_reason" => $rejectionReason])
            ->log($entity::class === Company::class ? "company_rejected" : "university_rejected");
    }

    private function sendAcceptMail(Company|University $entity): void
    {
        $mail = $entity instanceof Company ?
            new CompanyVerificationAcceptMail($entity) :
            new UniversityVerificationAcceptMail($entity);

        Mail::to($entity->email)->send($mail);
    }

    private function sendRejectMail(Company|University $entity, string $rejectionReason): void
    {
        $mail = $entity instanceof Company ?
            new CompanyVerificationRejectMail($entity, $rejectionReason) :
            new UniversityVerificationRejectMail($entity, $rejectionReason);

        Mail::to($entity->email)->send($mail);
    }
}
