<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("first_name")->nullable()->change();
            $table->string("last_name")->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("first_name")->nullable(false)->change();
            $table->string("last_name")->nullable(false)->change();
        });
    }
};
