<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            if (!Schema::hasColumn("users", "cv_path")) {
                $table->string("cv_path")->nullable();
            }

            if (!Schema::hasColumn("users", "onboarding_dismissed_at")) {
                $table->timestamp("onboarding_dismissed_at")->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            if (Schema::hasColumn("users", "onboarding_dismissed_at")) {
                $table->dropColumn("onboarding_dismissed_at");
            }

            if (Schema::hasColumn("users", "cv_path")) {
                $table->dropColumn("cv_path");
            }
        });
    }
};
