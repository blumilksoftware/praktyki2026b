<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Student\GetOfferDetailsAction;
use App\Actions\Student\GetSimilarOffersAction;
use App\Actions\Student\GetStudentOffersAction;
use App\Enums\UserRole;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function __construct(
        private readonly GetStudentOffersAction $getStudentOffersAction,
        private readonly GetOfferDetailsAction $getOfferDetailsAction,
        private readonly GetSimilarOffersAction $getSimilarOffersAction,
    ) {}

    public function index(): Response
    {
        /** @var ?User $user */
        $user = Auth::user();
        $isStudent = $user !== null && $user->role === UserRole::Student;

        $studyFields = StudyField::query()
            ->select(["id", "name"])
            ->orderBy("name")
            ->get()
            ->map(fn(StudyField $field): array => [
                "value" => $field->id,
                "label" => $field->name,
            ])
            ->all();

        return Inertia::render("Offers", [
            "offers" => $this->getStudentOffersAction->execute($isStudent ? $user : null)->values(),
            "hasCv" => $isStudent && $user->cv_path !== null,
            "studyFields" => $studyFields,
            "isGuest" => !$isStudent,
            "mapboxToken" => config("services.mapbox.access_token"),
        ]);
    }

    public function show(Request $request, Offer $offer): Response
    {
        $user = $request->user();
        $isStudent = $user !== null && $user->role === UserRole::Student;

        return inertia("Offers/Show", [
            "offer" => $this->getOfferDetailsAction->execute($offer, $isStudent ? $user : null),
            "similarOffers" => $this->getSimilarOffersAction->execute($offer),
            "hasCv" => $isStudent && $user->cv_path !== null,
            "isGuest" => !$isStudent,
        ]);
    }
}
