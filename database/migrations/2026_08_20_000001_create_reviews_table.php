<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("reviews", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("student_id")->constrained("users")->cascadeOnDelete();
            $table->foreignUuid("company_id")->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger("rating");
            $table->text("comment")->nullable();
            $table->boolean("hidden")->default(false);
            $table->timestamps();

            $table->unique(["student_id", "company_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("reviews");
    }
};
