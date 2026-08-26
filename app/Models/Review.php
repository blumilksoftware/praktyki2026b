<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $student_id
 * @property string $company_id
 * @property int $rating
 * @property ?string $comment
 * @property bool $hidden
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Review extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "student_id",
        "company_id",
        "rating",
        "comment",
        "hidden",
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, "student_id");
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @param Builder<Review> $query
     * @return Builder<Review>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where("hidden", false);
    }

    protected function casts(): array
    {
        return [
            "rating" => "integer",
            "hidden" => "boolean",
        ];
    }
}
