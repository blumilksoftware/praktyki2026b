<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("first_name");
            $table->string("last_name");
            $table->dropColumn("name");
            $table->enum("role", ["student", "superAdmin", "universityAdmin", "companyAdmin"]);
            $table->timestamp("terms_accepted_at")->nullable();
            $table->string("university")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn(["first_name", "last_name", "role", "terms_accepted_at", "university"]);
            $table->string("name");
        });
    }
};
