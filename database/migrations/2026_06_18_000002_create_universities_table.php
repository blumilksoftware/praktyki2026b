<?php

declare(strict_types=1);

use App\Enums\UniversityVerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("universities", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->string("name");
            $table->string("email");
            $table->string("domain")->unique();
            $table->string("address");
            $table->string("phone");
            $table->string("website")->nullable();
            $table->string("verification_status")->default(UniversityVerificationStatus::Pending->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("universities");
    }
};
