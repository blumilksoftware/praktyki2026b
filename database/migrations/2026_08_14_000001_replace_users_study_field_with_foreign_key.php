<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->foreignUuid("study_field_id")->nullable()->constrained()->nullOnDelete();
        });

        DB::table("study_fields")
            ->select(["id", "name"])
            ->get()
            ->each(function (object $field): void {
                DB::table("users")
                    ->whereNull("study_field_id")
                    ->where(
                        fn(Builder $query): Builder => $query
                            ->where("study_field", $field->id)
                            ->orWhere("study_field", $field->name),
                    )
                    ->update(["study_field_id" => $field->id]);
            });

        Schema::table("users", function (Blueprint $table): void {
            $table->dropColumn("study_field");
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->string("study_field")->nullable();
        });

        DB::table("study_fields")
            ->select(["id", "name"])
            ->get()
            ->each(function (object $field): void {
                DB::table("users")
                    ->where("study_field_id", $field->id)
                    ->update(["study_field" => $field->name]);
            });

        Schema::table("users", function (Blueprint $table): void {
            $table->dropConstrainedForeignId("study_field_id");
        });
    }
};
