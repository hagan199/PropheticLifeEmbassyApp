<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Drop the old enum check constraint on visitors.category so it works as a plain string column.
     */
    public function up(): void
    {
        // PostgreSQL: drop the auto-generated check constraint from the original enum definition
        $constraints = DB::select("
            SELECT con.conname
            FROM pg_constraint con
            JOIN pg_class rel ON rel.oid = con.conrelid
            JOIN pg_namespace nsp ON nsp.oid = rel.relnamespace
            WHERE rel.relname = 'visitors'
              AND con.contype = 'c'
              AND pg_get_constraintdef(con.oid) LIKE '%category%'
        ");

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE visitors DROP CONSTRAINT \"{$constraint->conname}\"");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-adding the constraint is not practical; the column is now a string
    }
};
