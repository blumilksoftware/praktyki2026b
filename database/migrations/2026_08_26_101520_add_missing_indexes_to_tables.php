<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("offers", function (Blueprint $table): void {
            $table->index("company_id");
            $table->index("status");
            $table->index("city");
            $table->index("work_mode");
            $table->index("start_date");
            $table->index("end_date");
        });

        Schema::table("applications", function (Blueprint $table): void {
            $table->index("student_id");
        });

        Schema::table("offer_study_field", function (Blueprint $table): void {
            $table->index("study_field_id");
        });

        Schema::table("offer_university", function (Blueprint $table): void {
            $table->index("university_id");
        });

        Schema::table("student_favourites", function (Blueprint $table): void {
            $table->index("offer_id");
        });

        Schema::table("student_study_field", function (Blueprint $table): void {
            $table->index("study_field_id");
        });

        Schema::table("reviews", function (Blueprint $table): void {
            $table->index("company_id");
        });

        Schema::table("partnerships", function (Blueprint $table): void {
            $table->index("university_id");
        });

        Schema::table("study_fields", function (Blueprint $table): void {
            $table->index("faculty_id");
        });

        Schema::table("faculties", function (Blueprint $table): void {
            $table->index("university_id");
        });

        Schema::table("email_verification_tokens", function (Blueprint $table): void {
            $table->index("user_id");
        });

        Schema::table("organization_invitations", function (Blueprint $table): void {
            $table->index("invited_by");
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->index("organization_id");
            $table->index("study_field_id");
        });
    }

    public function down(): void
    {
        Schema::table("offers", function (Blueprint $table): void {
            $table->dropIndex(["company_id"]);
            $table->dropIndex(["status"]);
            $table->dropIndex(["city"]);
            $table->dropIndex(["work_mode"]);
            $table->dropIndex(["start_date"]);
            $table->dropIndex(["end_date"]);
        });

        Schema::table("applications", function (Blueprint $table): void {
            $table->dropIndex(["student_id"]);
        });

        Schema::table("offer_study_field", function (Blueprint $table): void {
            $table->dropIndex(["study_field_id"]);
        });

        Schema::table("offer_university", function (Blueprint $table): void {
            $table->dropIndex(["university_id"]);
        });

        Schema::table("student_favourites", function (Blueprint $table): void {
            $table->dropIndex(["offer_id"]);
        });

        Schema::table("student_study_field", function (Blueprint $table): void {
            $table->dropIndex(["study_field_id"]);
        });

        Schema::table("reviews", function (Blueprint $table): void {
            $table->dropIndex(["company_id"]);
        });

        Schema::table("partnerships", function (Blueprint $table): void {
            $table->dropIndex(["university_id"]);
        });

        Schema::table("study_fields", function (Blueprint $table): void {
            $table->dropIndex(["faculty_id"]);
        });

        Schema::table("faculties", function (Blueprint $table): void {
            $table->dropIndex(["university_id"]);
        });

        Schema::table("email_verification_tokens", function (Blueprint $table): void {
            $table->dropIndex(["user_id"]);
        });

        Schema::table("organization_invitations", function (Blueprint $table): void {
            $table->dropIndex(["invited_by"]);
        });

        Schema::table("users", function (Blueprint $table): void {
            $table->dropIndex(["organization_id"]);
            $table->dropIndex(["study_field_id"]);
        });
    }
};
