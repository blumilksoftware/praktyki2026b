<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $faculty_id
 * @property string $name
 * @property-read int|null $students_count
 * @property-read int|null $offers_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class StudyField extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "name",
        "faculty_id",
    ];

    /**
     * @return BelongsTo<Faculty, $this>
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<Offer, $this>
     */
    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class);
    }
}
