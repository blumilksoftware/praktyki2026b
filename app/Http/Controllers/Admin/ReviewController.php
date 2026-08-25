<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteReview;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function __construct(
        private readonly DeleteReview $deleteReview,
    ) {}

    public function destroy(Review $review): RedirectResponse
    {
        $this->deleteReview->execute($review);

        return back();
    }
}
