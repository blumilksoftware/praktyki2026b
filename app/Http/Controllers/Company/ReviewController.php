<?php

declare(strict_types=1);

namespace App\Http\Controllers\Company;

use App\Actions\Company\HideReview;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function __construct(
        private readonly HideReview $hideReview,
    ) {}

    public function hide(Review $review): RedirectResponse
    {
        Gate::authorize("hide", $review);

        $this->hideReview->execute($review);

        return back();
    }
}
