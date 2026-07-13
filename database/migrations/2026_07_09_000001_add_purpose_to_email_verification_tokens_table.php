<?php

declare(strict_types=1);

use App\Enums\EmailVerificationTokenPurpose;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("email_verification_tokens", function (Blueprint $table): void {
            $table->string("purpose")->default(EmailVerificationTokenPurpose::Registration->value)->after("user_id");
        });
    }

    public function down(): void
    {
        Schema::table("email_verification_tokens", function (Blueprint $table): void {
            $table->dropColumn("purpose");
        });
    }
};
