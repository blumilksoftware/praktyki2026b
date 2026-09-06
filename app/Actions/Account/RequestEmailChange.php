<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\DB;

class RequestEmailChange
{
    public function __construct(
        private readonly EmailVerificationService $verificationService,
    ) {}

    public function execute(User $student, string $newEmail): void
    {
        DB::transaction(function () use ($student, $newEmail): void {
            $student->forceFill(["pending_email" => $newEmail])->save();

            $this->verificationService->sendEmailChangeVerification($student, $newEmail);
        });
    }
}
