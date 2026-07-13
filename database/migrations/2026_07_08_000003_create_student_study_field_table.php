<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("student_study_field", function (Blueprint $table): void {
            $table->foreignUuid("student_id")->constrained("users")->cascadeOnDelete();
            $table->foreignUuid("study_field_id")->constrained()->cascadeOnDelete();
            $table->primary(["student_id", "study_field_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("student_study_field");
    }
};
