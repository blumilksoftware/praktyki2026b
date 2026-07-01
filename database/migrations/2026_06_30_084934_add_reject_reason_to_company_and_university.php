<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->string("rejection_reason")->nullable();
        });

        Schema::table("companies", function (Blueprint $table): void {
            $table->string("rejection_reason")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->dropColumn("rejection_reason");
        });

        Schema::table("companies", function (Blueprint $table): void {
            $table->dropColumn("rejection_reason");
        });
    }
};
