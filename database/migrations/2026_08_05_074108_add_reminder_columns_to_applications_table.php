<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->timestamp('reminder_14_sent_at')->nullable()->after('status');
            $table->timestamp('reminder_28_sent_at')->nullable()->after('reminder_14_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropColumn(['reminder_14_sent_at', 'reminder_28_sent_at']);
        });
    }
};
