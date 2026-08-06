<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvitationStatus;
use App\Enums\OrganizationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $organization_id
 * @property OrganizationType $organization_type
 * @property string $email
 * @property ?string $invited_by
 * @property string $token
 * @property InvitationStatus $status
 * @property ?Carbon $accepted_at
 * @property ?Carbon $revoked_at
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OrganizationInvitation extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "organization_id",
        "organization_type",
        "email",
        "invited_by",
        "token",
        "status",
        "accepted_at",
        "revoked_at",
        "expires_at",
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "invited_by");
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    protected function casts(): array
    {
        return [
            "organization_type" => OrganizationType::class,
            "status" => InvitationStatus::class,
            "accepted_at" => "datetime",
            "revoked_at" => "datetime",
            "expires_at" => "datetime",
        ];
    }
}
