<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->index("role");
            $table->index("created_at");
        });

        Schema::table("offers", function (Blueprint $table): void {
            $table->index("created_at");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->dropIndex(["role"]);
            $table->dropIndex(["created_at"]);
        });

        Schema::table("offers", function (Blueprint $table): void {
            $table->dropIndex(["created_at"]);
        });
    }
};
