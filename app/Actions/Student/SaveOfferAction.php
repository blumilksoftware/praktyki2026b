<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Enums\OfferStatus;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class SaveOfferAction
{
    public function execute(User $student, Offer $offer): void
    {
        if ($offer->status !== OfferStatus::Published) {
            throw ValidationException::withMessages([
                "offer" => __("validation.offer_inactive"),
            ]);
        }

        try {
            $student->favourites()->attach($offer->id);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                "offer" => __("validation.already_saved"),
            ]);
        }
    }
}
