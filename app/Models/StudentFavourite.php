<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $student_id
 * @property string $offer_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class StudentFavourite extends Pivot
{
    protected $table = "student_favourites";
}
