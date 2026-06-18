<?php

declare(strict_types=1);

use App\Models\University;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->foreignIdFor(University::class, "organization_id")->nullable()->constrained("universities")->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(University::class, "organization_id");
        });
    }
};
