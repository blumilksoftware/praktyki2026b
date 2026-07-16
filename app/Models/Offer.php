<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OfferStatus;
use App\Enums\WorkMode;
use Carbon\Carbon;
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
 * @property bool $is_paid
 * @property int|null $salary_min
 * @property int|null $salary_max
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?Carbon $deleted_at
 * @property-read StudentFavourite $pivot
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

    public function studyFields(): BelongsToMany
    {
        return $this->belongsToMany(StudyField::class);
    }

    public function universities(): BelongsToMany
    {
        return $this->belongsToMany(University::class);
    }

    public function scopePublished($query)
    {
        return $query->where("status", OfferStatus::Published);
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
            "is_paid" => "boolean",
            "salary_min" => "integer",
            "salary_max" => "integer",
        ];
    }
}
