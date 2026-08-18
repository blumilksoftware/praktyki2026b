<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\CreateOffer;
use App\Actions\Company\GetOffersSummary;
use App\Actions\Company\GetOfferStatusCounts;
use App\Actions\Company\PublishOffer;
use App\Actions\Company\UpdateOffer;
use App\DTO\Offer\CreateOfferData;
use App\DTO\Offer\UpdateOfferData;
use App\Enums\OfferStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Models\Offer;
use App\Models\StudyField;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Response;

class OfferController extends Controller
{
    public function __construct(
        private readonly CreateOffer $createOffer,
        private readonly UpdateOffer $updateOffer,
        private readonly PublishOffer $publishOffer,
        private readonly GetOffersSummary $getOffersSummary,
        private readonly GetOfferStatusCounts $getOfferStatusCounts,
    ) {}

    public function index(Request $request): Response
    {
        $company = Auth::user()->company;

        $offers = $this->getOffersSummary->execute(
            $company,
            $request->string("sort", "created_at")->toString(),
            $request->string("direction", "desc")->toString(),
            OfferStatus::tryFrom($request->string("status")->toString()),
            $request->string("search")->toString() ?: null,
        );

        return inertia("Company/Offers", [
            "offers" => $offers,
            "isCompanyVerified" => $company->verification_status === VerificationStatus::Verified,
            "search" => $request->string("search")->toString(),
            "status" => $request->string("status")->toString(),
            "statusCounts" => $this->getOfferStatusCounts->execute($company),
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

        return $this->redirectAfterOfferAction()
            ->with("status", __("company.offers.form.offerCreated"));
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
                "accepted_applications_count" => $offer->acceptedApplications()->count(),
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

        $updated = $this->updateOffer->execute($offer, $data);

        $message = $updated->status === OfferStatus::Closed && $data->status !== OfferStatus::Closed
            ? __("company.offers.form.offerClosedNoSpots")
            : __("company.offers.form.saveSuccess");

        return $this->redirectAfterOfferAction()->with("status", $message);
    }

    public function publish(Offer $offer): RedirectResponse
    {
        Gate::authorize("update", $offer);

        $this->publishOffer->execute($offer);

        return $this->redirectAfterOfferAction();
    }

    public function deactivate(Offer $offer): RedirectResponse
    {
        Gate::authorize("update", $offer);

        $offer->update(["status" => OfferStatus::Closed]);

        return back();
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        Gate::authorize("delete", $offer);

        $offer->delete();

        return back();
    }

    private function redirectAfterOfferAction(): RedirectResponse
    {
        return redirect()->route("company.offers");
    }

    private function formOptions(): array
    {
        return [
            "studyFields" => StudyField::query()->orderBy("name")->get(["id", "name"]),
            "universities" => University::query()->verified()->orderBy("name")->get(["id", "name"]),
            "isCompanyVerified" => Auth::user()->company?->verification_status === VerificationStatus::Verified,
        ];
    }
}
