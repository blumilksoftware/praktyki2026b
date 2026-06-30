<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->string("logo_path")->nullable();
            $table->string("external_form_url")->nullable();
        });
    }

    public function down(): void
    {
        Schema::table("universities", function (Blueprint $table): void {
            $table->dropColumn(["logo_path", "external_form_url"]);
        });
    }
};
