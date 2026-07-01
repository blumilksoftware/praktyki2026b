<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        if (!$offer->is_active) {
            throw ValidationException::withMessages([
                "offer" => __("validation.offer_inactive"),
            ]);
        }

        if ($offer->spots <= 0) {
            throw ValidationException::withMessages([
                "offer" => __("validation.no_spots_available"),
            ]);
        }

        return DB::transaction(function () use ($student, $offer): Application {
            $updated = DB::table("offers")
                ->where("id", $offer->id)
                ->where("spots", ">", 0)
                ->decrement("spots");

            if (!$updated) {
                throw ValidationException::withMessages([
                    "offer" => __("validation.no_spots_available"),
                ]);
            }

            return Application::create([
                "offer_id" => $offer->id,
                "student_id" => $student->id,
                "status" => ApplicationStatus::Pending,
            ]);
        });
    }
}
