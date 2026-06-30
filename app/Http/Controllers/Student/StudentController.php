<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\Student\DeleteCvAction;
use App\Actions\Student\UploadCvAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadCvRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function __construct(
        private readonly UploadCvAction $uploadCvAction,
        private readonly DeleteCvAction $deleteCvAction,
    ) {}

    public function uploadCv(UploadCvRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->uploadCvAction->execute($user, $request->file("cv"));

        return back();
    }

    public function deleteCv(): RedirectResponse
    {
        $user = Auth::user();

        $this->deleteCvAction->execute($user);

        return back();
    }
}
