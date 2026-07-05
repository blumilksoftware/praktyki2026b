<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("offer_university", function (Blueprint $table): void {
            $table->foreignUuid("offer_id")->constrained()->cascadeOnDelete();
            $table->foreignUuid("university_id")->constrained()->cascadeOnDelete();
            $table->primary(["offer_id", "university_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("offer_university");
    }
};
