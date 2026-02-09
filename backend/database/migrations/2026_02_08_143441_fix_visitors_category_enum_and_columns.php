<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'service_type')) {
                $table->string('service_type')->nullable();
            }
            if (!Schema::hasColumn('visitors', 'occupation')) {
                $table->string('occupation')->nullable();
            }
            // Change category to string to support 'Wants to be a Member'
            $table->string('category')->default('Visitor')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Reverting to enum might be tricky in Postgres, better just keep as string or revert manually
        });
    }
};
