<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("offers", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("company_id")->constrained()->cascadeOnDelete();
            $table->string("title");
            $table->text("description");
            $table->unsignedInteger("spots");
            $table->boolean("is_active")->default(true);
            $table->string("city");
            $table->decimal("latitude", 10, 7);
            $table->decimal("longitude", 10, 7);
            $table->date("start_date");
            $table->date("end_date");
            $table->string("work_mode");
            $table->string("status")->default("draft");
            $table->boolean("is_paid")->default(false);
            $table->unsignedInteger("salary_min")->nullable();
            $table->unsignedInteger("salary_max")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("offers");
    }
};
