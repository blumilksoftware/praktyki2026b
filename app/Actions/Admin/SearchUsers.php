<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchUsers
{
    public function execute(string $role, string $search): LengthAwarePaginator
    {
        $query = User::query()->where("status", "!=", "deleted");

        if ($role !== "all") {
            $query->where("role", $role);
        }

        if ($search !== "") {
            $query->where(function ($builder) use ($search): void {
                $builder->where("first_name", "like", "%{$search}%")
                    ->orWhere("last_name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%");
            });
        }

        return $query->orderBy("created_at", "desc")
            ->orderByDesc("id")
            ->paginate(20)->appends([
            "role" => $role,
            "search" => $search,
        ]);
    }
}
