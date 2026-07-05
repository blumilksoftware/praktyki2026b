<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("offer_study_field", function (Blueprint $table): void {
            $table->foreignUuid("offer_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("study_field_id")->constrained()->cascadeOnDelete();
            $table->primary(["offer_id", "study_field_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("offer_study_field");
    }
};
