<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Student\GetOfferDetailsAction;
use App\Actions\Student\GetSimilarOffersAction;
use App\Actions\Student\GetStudentOffersAction;
use App\Actions\Student\GetStudentOffersForMapAction;
use App\Actions\Student\GetStudentOffersMapCitiesAction;
use App\Enums\UserRole;
use App\Http\Requests\OfferFilterRequest;
use App\Models\Offer;
use App\Models\StudyField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function __construct(
        private readonly GetStudentOffersAction $getStudentOffersAction,
        private readonly GetOfferDetailsAction $getOfferDetailsAction,
        private readonly GetSimilarOffersAction $getSimilarOffersAction,
        private readonly GetStudentOffersForMapAction $getStudentOffersForMapAction,
        private readonly GetStudentOffersMapCitiesAction $getStudentOffersMapCitiesAction,
    ) {}

    public function index(OfferFilterRequest $request): Response
    {
        $user = Auth::user();
        $isStudent = $user !== null && $user->role === UserRole::Student;
        $filters = $request->validated();

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
            "offers" => $this->getStudentOffersAction->execute($isStudent ? $user : null, $filters, 15),
            "filters" => $filters,
            "hasCv" => $isStudent && $user->cv_path !== null,
            "studyFields" => $studyFields,
            "isGuest" => $user === null,
            "canApply" => $isStudent,
            "mapboxToken" => config("services.mapbox.access_token"),
            "radiusOptions" => [10, 25, 50, 100],
        ]);
    }

    public function map(OfferFilterRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $isNarrowedDown = !empty($filters["cities"] ?? [])
            || $this->getStudentOffersForMapAction->hasRadiusFilter($filters);

        if (!$isNarrowedDown) {
            return response()->json(
                $this->getStudentOffersMapCitiesAction->execute($filters),
            );
        }

        $user = Auth::user();
        $isStudent = $user !== null && $user->role === UserRole::Student;

        return response()->json(
            $this->getStudentOffersForMapAction->execute(
                $isStudent ? $user : null,
                $filters,
            ),
        );
    }

    public function show(Request $request, Offer $offer): Response
    {
        $user = $request->user();
        $isStudent = $user !== null && $user->role === UserRole::Student;

        return inertia("Offers/Show", [
            "offer" => $this->getOfferDetailsAction->execute($offer, $isStudent ? $user : null),
            "similarOffers" => $this->getSimilarOffersAction->execute($offer),
            "hasCv" => $isStudent && $user->cv_path !== null,
            "isGuest" => $user === null,
            "canApply" => $isStudent,
        ]);
    }

    public function preview(Request $request, Offer $offer): Response
    {
        Gate::authorize("update", $offer);

        $user = $request->user();
        $isStudent = $user !== null && $user->role === UserRole::Student;

        return inertia("Offers/Show", [
            "offer" => $this->getOfferDetailsAction->execute($offer, $isStudent ? $user : null, true),
            "similarOffers" => $this->getSimilarOffersAction->execute($offer),
            "hasCv" => $isStudent && $user->cv_path !== null,
            "isGuest" => $user === null,
            "canApply" => $isStudent,
        ]);
    }
}
