<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Services\EmailVerificationService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $id
 * @property ?string $first_name
 * @property ?string $last_name
 * @property string $email
 * @property ?string $pending_email
 * @property string $password
 * @property UserRole $role
 * @property UserStatus $status
 * @property ?string $university
 * @property ?string $organization_id
 * @property ?string $google_id
 * @property ?Carbon $terms_accepted_at
 * @property ?Carbon $email_verified_at
 * @property ?string $cv_path
 * @property ?string $photo_path
 * @property ?string $street
 * @property ?string $postal_code
 * @property ?string $city
 * @property ?string $study_field_id
 * @property ?int $study_year
 * @property ?string $specialization
 * @property ?array $skills
 * @property ?array $work_modes
 * @property ?Carbon $onboarding_dismissed_at
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
        "pending_email",
        "password",
        "role",
        "status",
        "university",
        "organization_id",
        "google_id",
        "terms_accepted_at",
        "cv_path",
        "photo_path",
        "street",
        "postal_code",
        "city",
        "study_field_id",
        "study_year",
        "specialization",
        "skills",
        "work_modes",
        "onboarding_dismissed_at",
    ];
    protected $hidden = [
        "password",
        "remember_token",
    ];

    /**
     * @return BelongsTo<StudyField, $this>
     */
    public function studyField(): BelongsTo
    {
        return $this->belongsTo(StudyField::class);
    }

    /**
     * @return BelongsTo<University, $this>
     */
    public function universityOrganization(): BelongsTo
    {
        return $this->belongsTo(University::class, "organization_id");
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, "organization_id");
    }

    public function verificationTokens(): HasMany
    {
        return $this->hasMany(EmailVerificationToken::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, "student_id");
    }

    public function preferredCities(): HasMany
    {
        return $this->hasMany(StudentPreferredCity::class, "student_id");
    }

    public function preferredStudyFields(): BelongsToMany
    {
        return $this->belongsToMany(StudyField::class, "student_study_field", "student_id");
    }

    /**
     * @return BelongsToMany<Offer, $this, StudentFavourite>
     */
    public function favourites(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, "student_favourites", "student_id")
            ->withTrashed()
            ->using(StudentFavourite::class)
            ->withTimestamps();
    }

    /**
     * @param Builder<User> $query
     * @return Builder<User>
     */
    public function scopeLinkedToUniversity(Builder $query, University $university): Builder
    {
        return $query
            ->where("role", UserRole::Student)
            ->where("organization_id", $university->id);
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? "") . " " . ($this->last_name ?? "")) ?: $this->email;
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
            "onboarding_dismissed_at" => "datetime",
            "password" => "hashed",
            "skills" => "array",
            "work_modes" => "array",
        ];
    }
}
