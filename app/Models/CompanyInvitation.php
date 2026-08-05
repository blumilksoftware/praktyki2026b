<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyInvitationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $company_id
 * @property string $email
 * @property ?string $invited_by
 * @property string $token
 * @property CompanyInvitationStatus $status
 * @property ?Carbon $accepted_at
 * @property ?Carbon $revoked_at
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CompanyInvitation extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        "company_id",
        "email",
        "invited_by",
        "token",
        "status",
        "accepted_at",
        "revoked_at",
        "expires_at",
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

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
            "status" => CompanyInvitationStatus::class,
            "accepted_at" => "datetime",
            "revoked_at" => "datetime",
            "expires_at" => "datetime",
        ];
    }
}
