<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchesCaseInsensitively
{
    protected function applyCaseInsensitiveSearch(Builder $query, string $search, array $columns): void
    {
        $term = "%" . addcslashes(mb_strtolower($search), "%_\\") . "%";

        $query->where(function (Builder $builder) use ($term, $columns): void {
            foreach ($columns as $column) {
                if (str_contains($column, ".")) {
                    [$relation, $relatedColumn] = explode(".", $column, 2);

                    $builder->orWhereHas(
                        $relation,
                        fn(Builder $related) => $related->whereRaw("LOWER({$relatedColumn}) LIKE ? ESCAPE '\\'", [$term]),
                    );

                    continue;
                }

                $builder->orWhereRaw("LOWER({$column}) LIKE ? ESCAPE '\\'", [$term]);
            }
        });
    }
}
