<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateStudentAccount;
use App\DTO\Auth\StudentRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRegistrationRequest;
use App\Mail\StudentRegistrationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class StudentRegistrationController extends Controller
{
    public function __construct(
        private readonly CreateStudentAccount $createStudentAccount,
    ) {}

    public function __invoke(StudentRegistrationRequest $request): RedirectResponse
    {
        $data = StudentRegistrationData::fromArray($request->validated());
        $user = $this->createStudentAccount->execute($data);

        Mail::to($user->email)->queue(new StudentRegistrationMail($user));
        $user->sendEmailVerificationNotification();

        return redirect()->route("login");
    }
}
