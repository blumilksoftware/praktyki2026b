<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("companies", function (Blueprint $table): void {
            $table->string("logo_path")->nullable();
            $table->text("description")->nullable();
            $table->json("tags")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("companies", function (Blueprint $table): void {
            $table->dropColumn(["logo_path", "description", "tags"]);
        });
    }
};
