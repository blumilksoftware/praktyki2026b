<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Enums\WorkMode;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $company_id
 * @property string $title
 * @property string $description
 * @property int $spots
 * @property string $city
 * @property float $latitude
 * @property float $longitude
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property WorkMode $work_mode
 * @property OfferStatus $status
 * @property ?Carbon $published_at
 * @property bool $is_paid
 * @property int|null $salary_min
 * @property int|null $salary_max
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property-read StudentFavourite $pivot
 * @property-read int|null $accepted_applications_count
 */
class Offer extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        "company_id",
        "title",
        "description",
        "spots",
        "city",
        "latitude",
        "longitude",
        "start_date",
        "end_date",
        "work_mode",
        "status",
        "published_at",
        "is_paid",
        "salary_min",
        "salary_max",
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function acceptedApplications(): HasMany
    {
        return $this->hasMany(Application::class)->where("status", ApplicationStatus::Accepted);
    }

    public function remainingSpots(): int
    {
        $accepted = $this->accepted_applications_count ?? $this->acceptedApplications()->count();

        return max(0, $this->spots - (int)$accepted);
    }

    public function studyFields(): BelongsToMany
    {
        return $this->belongsToMany(StudyField::class);
    }

    public function universities(): BelongsToMany
    {
        return $this->belongsToMany(University::class);
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where("status", OfferStatus::Published);
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeWithRemainingSpots(Builder $query): Builder
    {
        return $query->where(
            "spots",
            ">",
            fn(QueryBuilder $subQuery): QueryBuilder => $subQuery
                ->selectRaw("count(*)")
                ->from("applications")
                ->whereColumn("applications.offer_id", "offers.id")
                ->where("applications.status", ApplicationStatus::Accepted),
        );
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeWithoutRemainingSpots(Builder $query): Builder
    {
        return $query->where(
            "spots",
            "<=",
            fn(QueryBuilder $subQuery): QueryBuilder => $subQuery
                ->selectRaw("count(*)")
                ->from("applications")
                ->whereColumn("applications.offer_id", "offers.id")
                ->where("applications.status", ApplicationStatus::Accepted),
        );
    }

    /**
     * @param Builder<Offer> $query
     * @param array<int, string> $studyFieldIds
     * @return Builder<Offer>
     */
    public function scopeForStudyFields(Builder $query, array $studyFieldIds): Builder
    {
        if (empty($studyFieldIds)) {
            return $query;
        }

        return $query->whereHas(
            "studyFields",
            fn(Builder $studyFieldQuery): Builder => $studyFieldQuery->whereIn("study_fields.id", $studyFieldIds),
        );
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeForWorkMode(Builder $query, ?WorkMode $workMode): Builder
    {
        if ($workMode === null) {
            return $query;
        }

        return $query->where("work_mode", $workMode);
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeForCity(Builder $query, ?string $city): Builder
    {
        if ($city === null) {
            return $query;
        }

        return $query->where("city", $city);
    }

    /**
     * @param Builder<Offer> $query
     * @return Builder<Offer>
     */
    public function scopeForDateRange(Builder $query, ?string $dateFrom, ?string $dateTo, int $flexDays = 0): Builder
    {
        if ($dateFrom !== null) {
            $query->where("end_date", ">=", Carbon::parse($dateFrom)->subDays($flexDays));
        }

        if ($dateTo !== null) {
            $query->where("start_date", "<=", Carbon::parse($dateTo)->addDays($flexDays));
        }

        return $query;
    }

    protected function casts(): array
    {
        return [
            "spots" => "integer",
            "latitude" => "float",
            "longitude" => "float",
            "start_date" => "date",
            "end_date" => "date",
            "work_mode" => WorkMode::class,
            "status" => OfferStatus::class,
            "published_at" => "datetime",
            "is_paid" => "boolean",
            "salary_min" => "integer",
            "salary_max" => "integer",
        ];
    }
}
