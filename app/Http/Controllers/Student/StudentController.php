<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Actions\Student\BuildStudentProfileData;
use App\Actions\Account\ChangePassword;
use App\Actions\Student\DeleteCvAction;
use App\Actions\Student\DeleteStudentAccount;
use App\Actions\Student\DeleteStudentPhotoAction;
use App\Actions\Student\GetFavourites;
use App\Actions\Student\GetStudentApplicationsAction;
use App\Actions\Student\GetStudentOffersAction;
use App\Actions\Student\LinkStudentToUniversity;
use App\Actions\Account\RequestEmailChange;
use App\Actions\Student\SaveOfferAction;
use App\Actions\Student\SubmitCompanyReviewAction;
use App\Actions\Student\UnsaveOfferAction;
use App\Actions\Student\UpdateStudentProfile;
use App\Actions\Student\UploadCvAction;
use App\Actions\Student\UploadStudentPhotoAction;
use App\Actions\Student\WithdrawOfferAction;
use App\Actions\University\BuildFacultiesData;
use App\Actions\University\SearchUniversities;
use App\DTO\Student\UpdateStudentProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\LinkUniversityRequest;
use App\Http\Requests\SearchUniversitiesRequest;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Http\Requests\UploadCvRequest;
use App\Http\Requests\UploadStudentPhotoRequest;
use App\Models\Company;
use App\Models\Offer;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudentController extends Controller
{
    public function __construct(
        private readonly UploadCvAction $uploadCvAction,
        private readonly DeleteCvAction $deleteCvAction,
        private readonly ApplyToOfferAction $applyToOfferAction,
        private readonly WithdrawOfferAction $withdrawOfferAction,
        private readonly UpdateStudentProfile $updateStudentProfile,
        private readonly UploadStudentPhotoAction $uploadStudentPhotoAction,
        private readonly DeleteStudentPhotoAction $deleteStudentPhotoAction,
        private readonly ChangePassword $changePassword,
        private readonly RequestEmailChange $requestEmailChange,
        private readonly DeleteStudentAccount $deleteStudentAccount,
        private readonly SaveOfferAction $saveOfferAction,
        private readonly UnsaveOfferAction $unsaveOfferAction,
        private readonly SubmitCompanyReviewAction $submitCompanyReviewAction,
        private readonly GetFavourites $getFavourites,
        private readonly BuildStudentProfileData $buildStudentProfileData,
        private readonly GetStudentApplicationsAction $getStudentApplicationsAction,
        private readonly GetStudentOffersAction $getStudentOffersAction,
        private readonly SearchUniversities $searchUniversities,
        private readonly LinkStudentToUniversity $linkStudentToUniversity,
        private readonly BuildFacultiesData $buildFacultiesData,
    ) {}

    public function index(): Response
    {
        $user = Auth::user();
        $offers = $this->getStudentOffersAction->execute($user);

        return inertia("Student/Dashboard", [
            "applications" => $this->getStudentApplicationsAction->execute($user),
            "offers" => $offers->take(3)->values(),
            "offersCount" => $offers->total(),
            "hasCv" => $user->cv_path !== null,
            "favoritesCount" => $user->favourites()->count(),
        ]);
    }

    public function applications(): Response
    {
        $user = Auth::user();

        return inertia("Student/Applications", [
            "applications" => $this->getStudentApplicationsAction->execute($user),
        ]);
    }

    public function profile(): Response
    {
        $user = Auth::user();

        return inertia("Student/Profile", $this->buildStudentProfileData->execute($user));
    }

    public function editProfile(): Response
    {
        $user = Auth::user();

        return inertia("Student/ProfileEdit", $this->buildStudentProfileData->execute($user));
    }

    public function settings(): Response
    {
        $user = Auth::user();

        return inertia("Student/Settings", [
            "email" => $user->email,
            "emailVerifiedAt" => $user->email_verified_at?->toIso8601String(),
            "pendingEmail" => $user->pending_email,
        ]);
    }

    public function showPhoto(): StreamedResponse
    {
        $user = Auth::user();

        if ($user->photo_path === null) {
            throw new NotFoundHttpException();
        }

        $disk = config("filesystems.default", "local");

        if (!Storage::disk($disk)->exists($user->photo_path)) {
            throw new NotFoundHttpException();
        }

        return Storage::disk($disk)->response($user->photo_path);
    }

    public function updateProfile(UpdateStudentProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = UpdateStudentProfileData::fromArray($request->getData());

        $this->updateStudentProfile->execute($user, $data);

        return redirect()->route("student.profile");
    }

    public function searchUniversities(SearchUniversitiesRequest $request): JsonResponse
    {
        return response()->json([
            "universities" => $this->searchUniversities->execute($request->string("query")->toString()),
        ]);
    }

    public function universityFaculties(string $university): JsonResponse
    {
        return response()->json([
            "faculties" => $this->buildFacultiesData->execute(University::verified()->findOrFail($university)),
        ]);
    }

    public function linkUniversity(LinkUniversityRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->linkStudentToUniversity->execute($user, $request->string("university")->toString());

        return redirect()->route("student.profile");
    }

    public function uploadPhoto(UploadStudentPhotoRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $this->uploadStudentPhotoAction->execute($user, $request->file("photo"));

        return redirect()->route("student.profile");
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

    public function previewCv(): StreamedResponse
    {
        $user = Auth::user();

        if (!$user->cv_path) {
            throw new NotFoundHttpException();
        }

        $disk = config("filesystems.default", "local");

        if (!Storage::disk($disk)->exists($user->cv_path)) {
            throw new NotFoundHttpException();
        }

        return Storage::disk($disk)->response($user->cv_path, "CV.pdf", [
            "Content-Type" => "application/pdf",
            "Content-Disposition" => 'inline; filename="CV.pdf"',
        ]);
    }

    public function apply(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->applyToOfferAction->execute($user, $offer);

        return back();
    }

    public function withdraw(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->withdrawOfferAction->execute($user, $offer);

        return back();
    }

    public function saveOffer(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->saveOfferAction->execute($user, $offer);

        return back();
    }

    public function unsaveOffer(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->unsaveOfferAction->execute($user, $offer);

        return back();
    }

    public function reviewCompany(StoreReviewRequest $request, Company $company): RedirectResponse
    {
        $user = Auth::user();

        $this->submitCompanyReviewAction->execute(
            $user,
            $company,
            $request->integer("rating"),
            $request->string("comment")->toString() ?: null,
        );

        return back();
    }

    public function favourites(): Response
    {
        $user = Auth::user();

        return inertia("Student/Favourites", [
            "favourites" => $this->getFavourites->execute($user),
        ]);
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
