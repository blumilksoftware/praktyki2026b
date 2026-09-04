<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\User;
use App\Traits\SearchesCaseInsensitively;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchUsers
{
    use SearchesCaseInsensitively;

    public function execute(array $filters): LengthAwarePaginator
    {
        $query = User::query()->where("status", "!=", "deleted");

        if ($filters["role"] !== "all") {
            $query->where("role", $filters["role"]);
        }

        if ($filters["search"] !== "") {
            $this->applyCaseInsensitiveSearch($query, $filters["search"], ["first_name", "last_name", "email"]);
        }

        if ($filters["sort_key"] === "name") {
            $query->orderBy("first_name", $filters["sort_dir"])->orderBy("last_name", $filters["sort_dir"]);
        } else {
            $query->orderBy($filters["sort_key"], $filters["sort_dir"]);
        }

        $paginator = $query->orderByDesc("id")->paginate(20)->appends($filters);

        return $paginator->through(fn(User $user): array => [
            ...$user->toArray(),
            "photo_url" => $user->photo_path ? route("admin.users.photo", ["user" => $user]) : null,
        ]);
    }
}
