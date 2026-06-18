<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Services\EmailVerificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property ?string $first_name
 * @property ?string $last_name
 * @property string $email
 * @property string $password
 * @property UserRole $role
 * @property UserStatus $status
 * @property ?string $university
 * @property ?string $organization_id
 * @property ?Carbon $terms_accepted_at
 * @property ?Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;

    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "password",
        "role",
        "status",
        "university",
        "organization_id",
        "terms_accepted_at",
    ];
    protected $hidden = [
        "password",
        "remember_token",
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, "organization_id");
    }
    
    public function verificationTokens(): HasMany
    {
        return $this->hasMany(EmailVerificationToken::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        app(EmailVerificationService::class)->sendVerificationEmail($this);
    }

    protected function casts(): array
    {
        return [
            "role" => UserRole::class,
            "status" => UserStatus::class,
            "email_verified_at" => "datetime",
            "terms_accepted_at" => "datetime",
            "password" => "hashed",
        ];
    }
}
