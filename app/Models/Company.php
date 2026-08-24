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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $nip
 * @property string $email
 * @property string $street
 * @property string $postal_code
 * @property string $city
 * @property string $phone
 * @property ?string $website
 * @property ?string $logo_path
 * @property ?string $description
 * @property ?array $tags
 * @property VerificationStatus $verification_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $rejection_reason
 */
class Company extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        "name",
        "nip",
        "email",
        "street",
        "postal_code",
        "city",
        "phone",
        "website",
        "logo_path",
        "description",
        "tags",
        "verification_status",
        "rejection_reason",
    ];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, "organization_id");
    }

    /**
     * @return HasMany<Offer, $this>
     */
    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function applications(): HasManyThrough
    {
        return $this->hasManyThrough(Application::class, Offer::class)->withTrashedParents();
    }

    /**
     * @return HasMany<Partnership, $this>
     */
    public function partnerships(): HasMany
    {
        return $this->hasMany(Partnership::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeNeedingVerification($query)
    {
        return $query->where("verification_status", VerificationStatus::Pending);
    }

    /**
     * @return Builder<Company>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where("verification_status", VerificationStatus::Verified);
    }

    protected function casts(): array
    {
        return [
            "verification_status" => VerificationStatus::class,
            "tags" => "array",
        ];
    }
}
