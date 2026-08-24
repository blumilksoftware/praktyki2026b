<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\HideReview;
use App\Actions\Company\UnhideReview;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function __construct(
        private readonly HideReview $hideReview,
        private readonly UnhideReview $unhideReview,
    ) {}

    public function hide(Review $review): RedirectResponse
    {
        Gate::authorize("hide", $review);

        $this->hideReview->execute($review);

        return back();
    }

    public function unhide(Review $review): RedirectResponse
    {
        Gate::authorize("unhide", $review);

        $this->unhideReview->execute($review);

        return back();
    }
}
