<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Company;
use App\Models\Review;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitCompanyReviewAction
{
    public function execute(User $student, Company $company, int $rating, ?string $comment): Review
    {
        $hasAcceptedApplication = Application::query()
            ->where("student_id", $student->id)
            ->where("status", ApplicationStatus::Accepted)
            ->whereHas("offer", fn($query) => $query->where("company_id", $company->id))
            ->exists();

        if (!$hasAcceptedApplication) {
            throw ValidationException::withMessages([
                "company" => __("validation.review_requires_accepted_application"),
            ]);
        }

        $alreadyReviewed = Review::where("student_id", $student->id)
            ->where("company_id", $company->id)
            ->exists();

        if ($alreadyReviewed) {
            throw ValidationException::withMessages([
                "company" => __("validation.already_reviewed_company"),
            ]);
        }

        return Review::create([
            "student_id" => $student->id,
            "company_id" => $company->id,
            "rating" => $rating,
            "comment" => $comment,
        ]);
    }
}
