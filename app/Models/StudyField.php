<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $faculty_id
 * @property string $name
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

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
