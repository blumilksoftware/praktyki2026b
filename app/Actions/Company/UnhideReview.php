<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Review;

class UnhideReview
{
    public function execute(Review $review): void
    {
        $review->update(["hidden" => false]);
    }
}
