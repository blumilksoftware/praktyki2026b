<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Review;

class HideReview
{
    public function execute(Review $review): void
    {
        $review->update(["hidden" => true]);
    }
}
