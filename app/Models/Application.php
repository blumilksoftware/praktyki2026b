<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $offer_id
 * @property string $student_id
 * @property ApplicationStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Application extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "offer_id",
        "student_id",
        "status",
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, "student_id");
    }

    protected function casts(): array
    {
        return [
            "status" => ApplicationStatus::class,
        ];
    }
}
