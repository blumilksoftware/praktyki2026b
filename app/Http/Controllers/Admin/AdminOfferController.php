<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SearchOffers;
use App\Actions\Admin\TakeDownOfferAction;
use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class AdminOfferController extends Controller
{
    public function __construct(
        private readonly TakeDownOfferAction $takeDownAction,
        private readonly SearchOffers $searchOffers,
    ) {}

    public function index(Request $request): Response
    {
        $statusFilter = $request->query("status", "all");

        if (!is_string($statusFilter) || !mb_check_encoding($statusFilter, "UTF-8")) {
            $statusFilter = "all";
        }

        $searchQuery = $request->query("search", "");

        if (!is_string($searchQuery) || !mb_check_encoding($searchQuery, "UTF-8")) {
            $searchQuery = "";
        }

        return inertia("Admin/Offers", [
            "offers" => $this->searchOffers->execute($statusFilter, $searchQuery),
            "filters" => [
                "status" => $statusFilter,
                "search" => $searchQuery,
            ],
            "statuses" => array_map(fn(OfferStatus $status): string => $status->value, OfferStatus::cases()),
            "meta" => [
                "title" => "Admin Offers",
            ],
        ]);
    }

    public function takeDown(Offer $offer): RedirectResponse
    {
        if ($offer->status !== OfferStatus::Published) {
            return back()->withErrors(__("validation.offer_not_published"));
        }

        $this->takeDownAction->execute($offer, auth()->user());

        return back();
    }
}
