<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\CreateOffer;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\PublishOffer;
use App\Actions\Company\UpdateOffer;
use App\Actions\Offer\SearchOffers;
use App\DTO\Offer\CreateOfferData;
use App\DTO\Offer\UpdateOfferData;
use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;


class OfferController extends Controller
{
    public function __construct(
        private readonly CreateOffer $createOffer,
        private readonly UpdateOffer $updateOffer,
        private readonly PublishOffer $publishOffer,
        private readonly GetOffersSummary $getOffersSummary,
    ) {}

    public function index(): Response
    {
        $company = Auth::user()->company;

        return Inertia::render("Company/Offers", [
            "offers" => $this->getOffersSummary->execute($company),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize("create", Offer::class);

        return inertia("Company/CreateOffer", $this->formOptions());
    }

    public function store(StoreOfferRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;
        $data = CreateOfferData::fromArray($request->getData());

        $this->createOffer->execute($company, $data);

        return redirect()->route("company.dashboard");
    }

    public function edit(Offer $offer): Response
    {
        Gate::authorize("update", $offer);

        return inertia("Company/EditOffer", [
            ...$this->formOptions(),
            "offer" => [
                "id" => $offer->id,
                "title" => $offer->title,
                "description" => $offer->description,
                "spots" => $offer->spots,
                "city" => $offer->city,
                "start_date" => $offer->start_date->toDateString(),
                "end_date" => $offer->end_date->toDateString(),
                "work_mode" => $offer->work_mode->value,
                "status" => $offer->status->value,
                "is_paid" => $offer->is_paid,
                "salary_min" => $offer->salary_min,
                "salary_max" => $offer->salary_max,
                "study_field_ids" => $offer->studyFields->pluck("id"),
                "university_ids" => $offer->universities->pluck("id"),
            ],
        ]);
    }

    public function update(StoreOfferRequest $request, Offer $offer): RedirectResponse
    {
        Gate::authorize("update", $offer);

        $data = UpdateOfferData::fromArray($request->getData());

        $this->updateOffer->execute($offer, $data);

        return redirect()->route("company.dashboard");
    }

    public function publish(Offer $offer): RedirectResponse
    {
        Gate::authorize("update", $offer);

        $this->publishOffer->execute($offer);

        return redirect()->route("company.dashboard");
    }

    public function deactivate(Offer $offer): RedirectResponse
    {
        Gate::authorize("update", $offer);

        $offer->update(["status" => OfferStatus::Closed]);

        return redirect()->route("company.dashboard");
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        Gate::authorize("delete", $offer);

        $offer->delete();

        return redirect()->route("company.dashboard");
    }

    private function formOptions(): array
    {
        return [
            "studyFields" => StudyField::query()->orderBy("name")->get(["id", "name"]),
            "universities" => University::query()->orderBy("name")->get(["id", "name"]),
        ];
    }
}
