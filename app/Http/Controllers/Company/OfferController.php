<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\CreateOffer;
use App\DTO\Offer\CreateOfferData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOfferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    public function __construct(
        private readonly CreateOffer $createOffer,
    ) {}

    public function store(CreateOfferRequest $request): RedirectResponse
    {
        $company = Auth::user()->company;
        $data = CreateOfferData::fromArray($request->getData());

        $this->createOffer->execute($company, $data);

        return redirect()->route("company.dashboard");
    }
}
