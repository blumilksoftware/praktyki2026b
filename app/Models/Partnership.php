<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PartnershipStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $company_id
 * @property string $university_id
 * @property PartnershipStatus $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Partnership extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "company_id",
        "university_id",
        "status",
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<University, $this>
     */
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    protected function casts(): array
    {
        return [
            "status" => PartnershipStatus::class,
        ];
    }
}
