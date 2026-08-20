<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\ApplicationStatus;
use App\Enums\OfferStatus;
use App\Mail\JobApplication\NewJobApplicationMail;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplyToOfferAction
{
    public function execute(User $student, Offer $offer): Application
    {
        if ($student->cv_path === null) {
            throw ValidationException::withMessages([
                "cv" => __("validation.student_no_cv"),
            ]);
        }

        $alreadyApplied = Application::where("offer_id", $offer->id)
            ->where("student_id", $student->id)
            ->exists();

        if ($alreadyApplied) {
            throw ValidationException::withMessages([
                "offer" => __("validation.already_applied"),
            ]);
        }

        if ($offer->status !== OfferStatus::Published) {
            throw ValidationException::withMessages([
                "offer" => __("validation.offer_inactive"),
            ]);
        }

        if ($offer->remainingSpots() <= 0) {
            throw ValidationException::withMessages([
                "offer" => __("validation.no_spots_available"),
            ]);
        }

        $disk = config("filesystems.default", "local");
        $extension = pathinfo($student->cv_path, PATHINFO_EXTENSION) ?: "pdf";
        $newPath = "applications/cvs/" . Str::random(40) . "." . $extension;

        $copied = false;

        if (Storage::disk($disk)->exists($student->cv_path)) {
            $copied = Storage::disk($disk)->copy($student->cv_path, $newPath);

            if (!$copied) {
                Log::warning("Failed to copy student's CV snapshot.", [
                    "student_id" => $student->id,
                    "source" => $student->cv_path,
                    "destination" => $newPath,
                    "disk" => $disk,
                ]);
            }
        } else {
            Log::warning("Student's CV file not found on disk.", [
                "student_id" => $student->id,
                "path" => $student->cv_path,
                "disk" => $disk,
            ]);
        }

        $application = Application::create([
            "offer_id" => $offer->id,
            "student_id" => $student->id,
            "status" => ApplicationStatus::Pending,
            "cv_path" => $copied ? $newPath : null,
        ]);

        $application->setRelation("student", $student);
        $application->loadMissing("offer.company");

        $companyUsers = $application->offer->company->users;

        Notification::send($companyUsers, new NewApplicationNotification($application));

        $applicationUrl = route("company.applications", [
            "offer" => $offer->id,
            "status" => "pending",
        ]);

        foreach ($companyUsers as $companyUser) {
            Mail::to($companyUser->email)->queue(
                new NewJobApplicationMail(
                    studentName: $student->fullName(),
                    jobTitle: $offer->title,
                    applicationUrl: $applicationUrl,
                ),
            );
        }

        return $application;
    }
}
