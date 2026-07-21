<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("photo_path")->nullable();
            $table->string("pending_email")->nullable();
            $table->unsignedTinyInteger("age")->nullable();
            $table->string("street")->nullable();
            $table->string("building_number")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("city")->nullable();
            $table->string("study_field")->nullable();
            $table->unsignedTinyInteger("study_year")->nullable();
            $table->string("specialization")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn(["photo_path", "pending_email", "age", "street", "building_number", "postal_code", "city", "study_field", "study_year", "specialization"]);
        });
    }
};
