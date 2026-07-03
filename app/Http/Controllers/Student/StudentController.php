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

    public function apply(Offer $offer): RedirectResponse
    {
        $user = Auth::user();

        $this->applyToOfferAction->execute($user, $offer);

        return back();
    }
}
