<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::drop("company_invitations");

        Schema::create("organization_invitations", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->uuid("organization_id");
            $table->string("organization_type");
            $table->string("email");
            $table->foreignUuid("invited_by")->nullable()->constrained("users")->nullOnDelete();
            $table->string("token")->unique();
            $table->string("status");
            $table->timestamp("accepted_at")->nullable();
            $table->timestamp("revoked_at")->nullable();
            $table->timestamp("expires_at");
            $table->timestamps();

            $table->unique(["organization_id", "organization_type", "email"]);
        });
    }

    public function down(): void
    {
        Schema::drop("organization_invitations");

        Schema::create("company_invitations", function (Blueprint $table): void {
            $table->uuid("id")->primary();
            $table->foreignUuid("company_id")->constrained()->cascadeOnDelete();
            $table->string("email");
            $table->foreignUuid("invited_by")->nullable()->constrained("users")->nullOnDelete();
            $table->string("token");
            $table->string("status");
            $table->timestamp("accepted_at")->nullable();
            $table->timestamp("revoked_at")->nullable();
            $table->timestamp("expires_at");
            $table->timestamps();

            $table->unique(["company_id", "email"]);
        });
    }
};
