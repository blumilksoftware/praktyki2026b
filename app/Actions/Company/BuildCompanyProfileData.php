<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Enums\ApplicationStatus;
use App\Enums\PartnershipStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Company;
use App\Models\Review;
use App\Models\User;

class BuildCompanyProfileData
{
    public function execute(Company $company, ?User $viewer = null): array
    {
        return [
            "id" => $company->id,
            "name" => $company->name,
            "logoUrl" => $company->logo_path,
            "tags" => $company->tags ?? [],
            "description" => $company->description,
            "email" => $company->email,
            "phone" => $company->phone,
            "website" => $company->website,
            "street" => $company->street,
            "postalCode" => $company->postal_code,
            "city" => $company->city,
            "nip" => $company->nip,
            "offers" => $company->offers()
                ->published()
                ->select("id", "title", "description", "spots")
                ->withCount("acceptedApplications")
                ->latest()
                ->get()
                ->map(fn($offer) => [
                    "id" => $offer->id,
                    "title" => $offer->title,
                    "description" => $offer->description,
                    "spots" => $offer->spots,
                    "remaining_spots" => $offer->remainingSpots(),
                ]),
            "verification_status" => $company->verification_status,
            "partners" => $company->partnerships()
                ->where("status", PartnershipStatus::Active)
                ->with("university:id,name,city")
                ->get()
                ->map(fn($partnership) => [
                    "id" => $partnership->university->id,
                    "name" => $partnership->university->name,
                    "city" => $partnership->university->city,
                ]),
            "reviews" => $this->buildReviews($company, $viewer),
        ];
    }

    private function buildReviews(Company $company, ?User $viewer): array
    {
        $isCompanyStaff = $viewer !== null && $viewer->organization_id === $company->id;
        $isSuperAdmin = $viewer !== null && $viewer->role === UserRole::SuperAdmin;

        $reviewsQuery = $company->reviews()->with("student:id,first_name,last_name")->latest();

        if (!$isCompanyStaff && !$isSuperAdmin) {
            $reviewsQuery->visible();
        }

        $hasReviewed = $viewer !== null
            && $viewer->role === UserRole::Student
            && $company->reviews()->where("student_id", $viewer->id)->exists();

        $canReview = $viewer !== null
            && $viewer->role === UserRole::Student
            && !$hasReviewed
            && Application::where("student_id", $viewer->id)
                ->where("status", ApplicationStatus::Accepted)
                ->whereHas("offer", fn($query) => $query->where("company_id", $company->id))
                ->exists();

        $reviewsAverage = $company->reviews()->visible()->avg("rating");

        return [
            "items" => $reviewsQuery->get()->map(fn(Review $review) => [
                "id" => $review->id,
                "rating" => $review->rating,
                "comment" => $review->comment,
                "studentName" => $review->student->fullName(),
                "createdAt" => $review->created_at->toIso8601String(),
                "hidden" => $review->hidden,
            ]),
            "averageRating" => $reviewsAverage !== null ? (float)$reviewsAverage : null,
            "count" => $company->reviews()->visible()->count(),
            "canReview" => $canReview,
            "hasReviewed" => $hasReviewed,
            "canModerate" => $isCompanyStaff,
            "canDelete" => $isSuperAdmin,
        ];
    }
}
