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
 * @property string $nip
 * @property string $email
 * @property string $street
 * @property string $building_number
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
 */
class Company extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "name",
        "nip",
        "email",
        "street",
        "building_number",
        "postal_code",
        "city",
        "phone",
        "website",
        "logo_path",
        "description",
        "tags",
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
            "tags" => "array",
        ];
    }
}
