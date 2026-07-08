<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Actions\Student\ApplyToOfferAction;
use App\Actions\Student\DeleteCvAction;
use App\Actions\Student\UploadCvAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadCvRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StudentController extends Controller
{
    public function __construct(
        private readonly UploadCvAction $uploadCvAction,
        private readonly DeleteCvAction $deleteCvAction,
        private readonly ApplyToOfferAction $applyToOfferAction,
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
}
