<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\IndustryTag;
use App\Models\User;

class DeleteIndustryTagAction
{
    public function execute(User $admin, IndustryTag $tag): void
    {
        $name = $tag->name;

        $tag->delete();

        activity()
            ->causedBy($admin)
            ->performedOn($tag)
            ->withProperties(["name" => $name])
            ->log("industry_tag_deleted");
    }
}
