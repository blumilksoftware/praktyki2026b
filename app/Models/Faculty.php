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
 * @property string $university_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Faculty extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "name",
        "university_id",
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function studyFields(): HasMany
    {
        return $this->hasMany(StudyField::class);
    }
}
