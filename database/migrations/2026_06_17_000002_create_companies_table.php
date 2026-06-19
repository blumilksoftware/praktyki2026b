<?php

declare(strict_types=1);

use App\Enums\VerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("companies", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->string("name");
            $table->string("nip")->unique();
            $table->string("email");
            $table->string("street");
            $table->string("building_number");
            $table->string("postal_code");
            $table->string("city");
            $table->string("phone");
            $table->string("website")->nullable();
            $table->string("verification_status")->default(VerificationStatus::Pending->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("companies");
    }
};
