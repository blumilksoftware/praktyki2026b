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

/**
 * @property string $id
 * @property string $company_id
 * @property string $title
 * @property string $description
 * @property int $spots
 * @property bool $is_active
 * @property string $city
 * @property float $latitude
 * @property float $longitude
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property WorkMode $work_mode
 * @property OfferStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Offer extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "company_id",
        "title",
        "description",
        "spots",
        "is_active",
        "city",
        "latitude",
        "longitude",
        "start_date",
        "end_date",
        "work_mode",
        "status",
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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
            "is_active" => "boolean",
            "latitude" => "float",
            "longitude" => "float",
            "start_date" => "date",
            "end_date" => "date",
            "work_mode" => WorkMode::class,
            "status" => OfferStatus::class,
        ];
    }
}
