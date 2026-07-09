<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $student_id
 * @property string $city
 * @property float $latitude
 * @property float $longitude
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class StudentPreferredCity extends Model
{
    use HasUuids;

    protected $fillable = [
        "student_id",
        "city",
        "latitude",
        "longitude",
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, "student_id");
    }

    protected function casts(): array
    {
        return [
            "latitude" => "float",
            "longitude" => "float",
        ];
    }
}
