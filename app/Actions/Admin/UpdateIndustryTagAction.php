<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\IndustryTag;
use App\Models\User;

class UpdateIndustryTagAction
{
    public function execute(User $admin, IndustryTag $tag, string $name): void
    {
        $oldName = $tag->name;

        $tag->update(["name" => $name]);

        activity()
            ->causedBy($admin)
            ->performedOn($tag)
            ->withProperties(["old_name" => $oldName, "new_name" => $tag->name])
            ->log("industry_tag_updated");
    }
}
