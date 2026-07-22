<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\CreateOffer;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\PublishOffer;
use App\DTO\Offer\CreateOfferData;
use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfferRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function __construct(
        private readonly CreateOffer $createOffer,
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

    public function store(CreateOfferRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;
        $data = CreateOfferData::fromArray($request->getData());

        $this->createOffer->execute($company, $data);

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
}
