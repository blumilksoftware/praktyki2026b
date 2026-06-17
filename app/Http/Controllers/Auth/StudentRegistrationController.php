<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateStudentAccount;
use App\DTO\Auth\StudentRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRegistrationRequest;
use App\Http\Resources\UserResource;
use App\Mail\StudentRegistrationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class StudentRegistrationController extends Controller
{
    public function __construct(
        private readonly CreateStudentAccount $createStudentAccount,
    ) {}

    public function __invoke(StudentRegistrationRequest $request): JsonResponse
    {
        $data = StudentRegistrationData::fromArray($request->validated());
        $user = $this->createStudentAccount->execute($data);

        Mail::to($user->email)->queue(new StudentRegistrationMail($user));

        return (new UserResource($user))->response()->setStatusCode(201);
    }
}
