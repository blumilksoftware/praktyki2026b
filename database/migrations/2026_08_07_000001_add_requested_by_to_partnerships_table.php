<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("partnerships", function (Blueprint $table): void {
            $table->string("requested_by")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("partnerships", function (Blueprint $table): void {
            $table->dropColumn("requested_by");
        });
    }
};
