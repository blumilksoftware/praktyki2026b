<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Actions\Student\ChangePassword;
use App\Actions\Student\DeleteCvAction;
use App\Actions\Student\DeleteStudentAccount;
use App\Actions\Student\DeleteStudentPhotoAction;
use App\Actions\Student\RequestEmailChange;
use App\Actions\Student\UpdateStudentProfile;
use App\Actions\Student\UploadCvAction;
use App\Actions\Student\UploadStudentPhotoAction;
use App\DTO\Student\UpdateStudentProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Http\Requests\UploadCvRequest;
use App\Http\Requests\UploadStudentPhotoRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function __construct(
        private readonly UploadCvAction $uploadCvAction,
        private readonly DeleteCvAction $deleteCvAction,
        private readonly ApplyToOfferAction $applyToOfferAction,
        private readonly UpdateStudentProfile $updateStudentProfile,
        private readonly UploadStudentPhotoAction $uploadStudentPhotoAction,
        private readonly DeleteStudentPhotoAction $deleteStudentPhotoAction,
        private readonly ChangePassword $changePassword,
        private readonly RequestEmailChange $requestEmailChange,
        private readonly DeleteStudentAccount $deleteStudentAccount,
    ) {}

    public function updateProfile(UpdateStudentProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = UpdateStudentProfileData::fromArray($request->getData());

        $this->updateStudentProfile->execute($user, $data);

        return back();
    }

    public function uploadPhoto(UploadStudentPhotoRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->uploadStudentPhotoAction->execute($user, $request->file("photo"));

        return back();
    }

    public function deletePhoto(): RedirectResponse
    {
        $user = Auth::user();

        $this->deleteStudentPhotoAction->execute($user);

        return back();
    }

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

    public function apply(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->applyToOfferAction->execute($user, $offer);

        return back();
    }

    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->changePassword->execute($user, $request->string("password")->toString());

        return back();
    }

    public function changeEmail(ChangeEmailRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->requestEmailChange->execute($user, $request->string("email")->toString());

        return back();
    }

    public function deleteAccount(DeleteAccountRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->deleteStudentAccount->execute($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/");
    }
}
