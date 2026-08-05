<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("role_temp")->nullable();
        });

        DB::table("users")->update(["role_temp" => DB::raw("role")]);

        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn("role");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->renameColumn("role_temp", "role");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->string("role")->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->enum("role_temp", ["student", "superAdmin", "universityAdmin", "companyAdmin"])->nullable();
        });

        DB::table("users")->update(["role_temp" => DB::raw("role")]);

        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn("role");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->renameColumn("role_temp", "role");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->string("role")->nullable(false)->change();
        });
    }
};
