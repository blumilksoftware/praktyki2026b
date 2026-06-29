<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("study_fields", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("faculty_id")->constrained("faculties")->cascadeOnDelete();
            $table->string("name");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("study_fields");
    }
};
