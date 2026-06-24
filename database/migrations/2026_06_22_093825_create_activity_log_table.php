<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("activity_log", function (Blueprint $table): void {
            $table->id();
            $table->string("log_name")->nullable()->index();
            $table->text("description");

            $table->uuid("subject_id")->nullable()->index();
            $table->string("subject_type")->nullable()->index();

            $table->string("event")->nullable();

            $table->uuid("causer_id")->nullable()->index();
            $table->string("causer_type")->nullable()->index();

            $table->json("attribute_changes")->nullable();
            $table->json("properties")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("activity_log");
    }
};
