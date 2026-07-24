<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Actions\Student\BuildStudentProfileData;
use App\Actions\Student\ChangePassword;
use App\Actions\Student\DeleteCvAction;
use App\Actions\Student\DeleteStudentAccount;
use App\Actions\Student\DeleteStudentPhotoAction;
use App\Actions\Student\GetFavourites;
use App\Actions\Student\GetStudentApplicationsAction;
use App\Actions\Student\LinkStudentToUniversity;
use App\Actions\Student\RequestEmailChange;
use App\Actions\Student\SaveOfferAction;
use App\Actions\Student\UnsaveOfferAction;
use App\Actions\Student\UpdateStudentProfile;
use App\Actions\Student\UploadCvAction;
use App\Actions\Student\UploadStudentPhotoAction;
use App\Actions\Student\WithdrawOfferAction;
use App\Actions\University\SearchUniversities;
use App\DTO\Student\UpdateStudentProfileData;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\LinkUniversityRequest;
use App\Http\Requests\SearchUniversitiesRequest;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Http\Requests\UploadCvRequest;
use App\Http\Requests\UploadStudentPhotoRequest;
use App\Models\Offer;
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
        private readonly GetFavourites $getFavourites,
        private readonly BuildStudentProfileData $buildStudentProfileData,
        private readonly GetStudentApplicationsAction $getStudentApplicationsAction,
        private readonly SearchUniversities $searchUniversities,
        private readonly LinkStudentToUniversity $linkStudentToUniversity,
    ) {}

    public function index(): Response
    {
        $user = Auth::user();

        return inertia("Student/Dashboard", [
            "applications" => $this->getStudentApplicationsAction->execute($user),
        ], [
            'offers' => $this->buildStudentOffers()->take(3)->values(),
        ]);
    }

    public function offers(): Response
    {
        return inertia("Student/Offers", [
            'offers' => $this->buildStudentOffers()->values(),
        ]);
    }

    public function favorites(): Response
    {
        return inertia("Student/Favorites", [
            'offers' => $this->buildStudentOffers()->values(),
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

    private function buildStudentOffers(): \Illuminate\Support\Collection
    {
        return Offer::published()
            ->with(['company', 'applications'])
            ->get()
            ->map(function (Offer $offer) {
                return [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'city' => $offer->city,
                    'work_mode' => $offer->work_mode->value ?? null,
                    'start_date' => $offer->start_date?->toDateString(),
                    'end_date' => $offer->end_date?->toDateString(),
                    'spots' => $offer->spots,
                    'remaining_spots' => max(0, $offer->spots - $offer->applications->count()),
                    'company' => [
                        'name' => $offer->company->name,
                        'logo_path' => $offer->company->logo_path,
                        'is_verified' => ($offer->company->verification_status ?? null) === VerificationStatus::Verified,
                    ],
                ];
            });
    }
}
