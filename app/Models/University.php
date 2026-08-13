<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $domain
 * @property string $street
 * @property string $postal_code
 * @property string $city
 * @property string $phone
 * @property ?string $website
 * @property ?string $description
 * @property ?string $logo_path
 * @property ?string $external_form_url
 * @property VerificationStatus $verification_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $rejection_reason
 */
class University extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "name",
        "email",
        "domain",
        "street",
        "postal_code",
        "city",
        "phone",
        "website",
        "description",
        "logo_path",
        "external_form_url",
        "verification_status",
        "rejection_reason",
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, "organization_id");
    }

    public function faculties(): HasMany
    {
        return $this->hasMany(Faculty::class);
    }

    /**
     * @return HasMany<Partnership, $this>
     */
    public function partnerships(): HasMany
    {
        return $this->hasMany(Partnership::class);
    }

    public function scopeNeedingVerification($query)
    {
        return $query->where("verification_status", VerificationStatus::Pending);
    }

    /**
     * @return Builder<University>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where("verification_status", VerificationStatus::Verified);
    }

    /**
     * @return Builder<University>
     */
    public function scopeMatchingName(Builder $query, string $name): Builder
    {
        return $query->where("name", "like", "%{$name}%");
    }

    protected function casts(): array
    {
        return [
            "verification_status" => VerificationStatus::class,
        ];
    }
}
