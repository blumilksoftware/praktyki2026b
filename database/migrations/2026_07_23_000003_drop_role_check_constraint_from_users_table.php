<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_role_check");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['student', 'superAdmin', 'universityAdmin', 'companyAdmin']::text[]))");
    }
};
