<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("student_favourites", function (Blueprint $table): void {
            $table->foreignUuid("student_id")->constrained("users")->cascadeOnDelete();
            $table->foreignUuid("offer_id")->constrained("offers")->cascadeOnDelete();
            $table->timestamps();

            $table->primary(["student_id", "offer_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("student_favourites");
    }
};
