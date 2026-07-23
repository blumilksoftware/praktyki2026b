<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Offer\SearchOffers;
use App\DTO\Offer\SearchOffersData;
use App\Http\Requests\SearchOffersRequest;
use App\Models\StudyField;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function __construct(
        private readonly SearchOffers $searchOffers,
    ) {}

    public function search(SearchOffersRequest $request): Response
    {
        $data = $this->searchOffers->execute(SearchOffersData::fromArray($request->getData()));

        $studyFields = StudyField::query()
            ->select(["id", "name"])
            ->orderBy("name")
            ->get()
            ->map(fn(StudyField $field): array => [
                "value" => $field->id,
                "label" => $field->name,
            ])
            ->all();

        return Inertia::render("Offers/Search", [
            "offers" => $data["offers"],
            "mapPoints" => $data["mapPoints"],
            "filters" => $request->validated(),
            "studyFields" => $studyFields,
        ]);
    }
}
