<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Application;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WithdrawOfferAction
{
    public function execute(User $student, Offer $offer): void
    {
        $application = Application::where("offer_id", $offer->id)
            ->where("student_id", $student->id)
            ->first();

        if (!$application) {
            throw ValidationException::withMessages([
                "offer" => __("validation.not_applied"),
            ]);
        }

        DB::transaction(function () use ($application, $offer): void {
            if ($application->cv_path) {
                $disk = config("filesystems.default", "local");

                if (Storage::disk($disk)->exists($application->cv_path)) {
                    Storage::disk($disk)->delete($application->cv_path);
                }
            }

            $application->delete();

            DB::table("offers")
                ->where("id", $offer->id)
                ->increment("spots");
        });
    }
}
