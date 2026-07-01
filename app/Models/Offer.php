<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $company_id
 * @property string $title
 * @property string $description
 * @property int $spots
 * @property bool $is_active
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
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    protected function casts(): array
    {
        return [
            "spots" => "integer",
            "is_active" => "boolean",
        ];
    }
}
