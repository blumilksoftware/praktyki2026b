<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $domain
 * @property string $address
 * @property string $phone
 * @property ?string $website
 * @property VerificationStatus $verification_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class University extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "name",
        "email",
        "domain",
        "address",
        "phone",
        "website",
        "verification_status",
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, "organization_id");
    }

    public function scopeNeedingVerification($query)
    {
        return $query->where("verification_status", VerificationStatus::Pending);
    }

    protected function casts(): array
    {
        return [
            "verification_status" => VerificationStatus::class,
        ];
    }
}
