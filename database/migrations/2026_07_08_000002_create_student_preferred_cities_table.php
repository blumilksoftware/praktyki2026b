<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("student_preferred_cities", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("student_id")->constrained("users")->cascadeOnDelete();
            $table->string("city");
            $table->decimal("latitude", 10, 7);
            $table->decimal("longitude", 10, 7);
            $table->timestamps();

            $table->unique(["student_id", "city"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("student_preferred_cities");
    }
};
