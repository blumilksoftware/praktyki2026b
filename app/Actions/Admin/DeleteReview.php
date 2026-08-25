<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Review;

class DeleteReview
{
    public function execute(Review $review): void
    {
        $review->delete();
    }
}
