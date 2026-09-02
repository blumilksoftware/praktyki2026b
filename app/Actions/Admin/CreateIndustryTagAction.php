<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\IndustryTag;
use App\Models\User;

class CreateIndustryTagAction
{
    public function execute(User $admin, string $name): IndustryTag
    {
        $tag = IndustryTag::query()->create(["name" => $name]);

        activity()
            ->causedBy($admin)
            ->performedOn($tag)
            ->withProperties(["name" => $tag->name])
            ->log("industry_tag_created");

        return $tag;
    }
}
