<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("applications", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("offer_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("student_id")->constrained("users")->cascadeOnDelete();
            $table->string("status")->default("pending");
            $table->string("cv_path")->nullable();
            $table->timestamps();

            $table->unique(["offer_id", "student_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("applications");
    }
};
