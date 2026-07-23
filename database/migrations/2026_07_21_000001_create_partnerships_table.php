<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("partnerships", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("company_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("university_id")->constrained()->cascadeOnDelete();
            $table->string("status");
            $table->timestamps();

            $table->unique(["company_id", "university_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("partnerships");
    }
};
