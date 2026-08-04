<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\VerificationStatus;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GetSimilarOffersAction
{
    private const int LIMIT = 4;

    /**
     * @return list<array<string, mixed>>
     */
    public function execute(Offer $offer): array
    {
        $offer->loadMissing(["studyFields"]);

        $studyFieldIds = $offer->studyFields->pluck("id")->all();
        $related = $this->relatedOffers($offer, $studyFieldIds);

        if ($related->count() < self::LIMIT) {
            $fallback = $this->fallbackOffers(
                excludeIds: $related->pluck("id")->push($offer->id)->all(),
                limit: self::LIMIT - $related->count(),
            );
            $related = $related->concat($fallback);
        }

        return $related
            ->take(self::LIMIT)
            ->map(fn(Offer $similarOffer): array => $this->mapOffer($similarOffer))
            ->values()
            ->all();
    }

    /**
     * @param list<string> $studyFieldIds
     * @return Collection<int, Offer>
     */
    private function relatedOffers(Offer $offer, array $studyFieldIds): Collection
    {
        return Offer::published()
            ->with(["company"])
            ->whereKeyNot($offer->id)
            ->where(function (Builder $query) use ($offer, $studyFieldIds): void {
                $query
                    ->where("city", $offer->city)
                    ->orWhere("company_id", $offer->company_id)
                    ->orWhere("work_mode", $offer->work_mode);

                if ($studyFieldIds !== []) {
                    $query->orWhereHas(
                        "studyFields",
                        fn(Builder $studyFieldQuery): Builder => $studyFieldQuery->whereIn("study_fields.id", $studyFieldIds),
                    );
                }
            })
            ->latest()
            ->limit(self::LIMIT)
            ->get();
    }

    /**
     * @param list<string> $excludeIds
     * @return Collection<int, Offer>
     */
    private function fallbackOffers(array $excludeIds, int $limit): Collection
    {
        if ($limit <= 0) {
            return collect();
        }

        return Offer::published()
            ->with(["company"])
            ->whereNotIn("id", $excludeIds)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOffer(Offer $offer): array
    {
        return [
            "id" => $offer->id,
            "title" => $offer->title,
            "city" => $offer->city,
            "work_mode" => $offer->work_mode->value ?? null,
            "start_date" => $offer->start_date?->toDateString(),
            "end_date" => $offer->end_date?->toDateString(),
            "company" => [
                "id" => $offer->company->id,
                "name" => $offer->company->name,
                "logo_path" => $offer->company->logo_path,
                "is_verified" => ($offer->company->verification_status ?? null) === VerificationStatus::Verified,
            ],
        ];
    }
}
