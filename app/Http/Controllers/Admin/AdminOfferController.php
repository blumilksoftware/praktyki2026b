<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SearchOffers;
use App\Actions\Admin\TakeDownOfferAction;
use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchOffersRequest;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class AdminOfferController extends Controller
{
    public function __construct(
        private readonly TakeDownOfferAction $takeDownAction,
        private readonly SearchOffers $searchOffers,
    ) {}

    public function index(SearchOffersRequest $request): Response
    {
        $filters = $request->getData();

        return inertia("Admin/Offers", [
            "offers" => $this->searchOffers->execute($filters["status"], $filters["search"]),
            "filters" => $filters,
            "statuses" => array_map(fn(OfferStatus $status): string => $status->value, OfferStatus::cases()),
            "meta" => [
                "title" => "Admin Offers",
            ],
        ]);
    }

    public function takeDown(Offer $offer): RedirectResponse
    {
        if ($offer->status !== OfferStatus::Published) {
            return back()->with("error", __("validation.offer_not_published"));
        }

        $this->takeDownAction->execute($offer, auth()->user());

        return back();
    }
}
