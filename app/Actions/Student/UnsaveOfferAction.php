<?php

declare(strict_types=1);

namespace App\Actions\Student;

use App\Models\Offer;
use App\Models\User;

class UnsaveOfferAction
{
    public function execute(User $student, Offer $offer): void
    {
        $student->favourites()->detach($offer->id);
    }
}
