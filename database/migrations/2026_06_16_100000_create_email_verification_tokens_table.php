<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create("email_verification_tokens", function (Blueprint $table): void {
            $table->id();
            $table->foreignId("user_id")->constrained()->cascadeOnDelete();
            $table->string("token");
            $table->timestamp("expires_at");
            $table->timestamp("created_at")->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("email_verification_tokens");
    }
};
