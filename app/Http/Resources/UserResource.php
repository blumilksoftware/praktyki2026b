<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            "id" => $user->id,
            "first_name" => $user->first_name,
            "last_name" => $user->last_name,
            "email" => $user->email,
            "role" => $user->role,
            "university" => $user->university,
            "email_verified_at" => $user->email_verified_at,
            "created_at" => $user->created_at,
        ];
    }
}
