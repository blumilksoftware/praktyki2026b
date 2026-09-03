<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileBackOrigin;
use Illuminate\Http\Request;

class ResolveProfileBackUrlAction
{
    public function execute(Request $request): ?string
    {
        $origin = ProfileBackOrigin::tryFrom((string)$request->query("from"));

        return $origin ? route($origin->routeName()) : null;
    }
}
