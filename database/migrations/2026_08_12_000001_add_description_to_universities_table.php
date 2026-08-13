<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->text("description")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->dropColumn("description");
        });
    }
};
