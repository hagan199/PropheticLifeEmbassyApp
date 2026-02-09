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
      if (!Schema::hasColumn('visitors', 'converted_user_id')) {
        $table->uuid('converted_user_id')->nullable()->after('created_by');
      }
      if (!Schema::hasColumn('visitors', 'converted_at')) {
        $table->timestamp('converted_at')->nullable()->after('converted_user_id');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('visitors', function (Blueprint $table) {
      if (Schema::hasColumn('visitors', 'converted_at')) {
        $table->dropColumn('converted_at');
      }
      if (Schema::hasColumn('visitors', 'converted_user_id')) {
        $table->dropColumn('converted_user_id');
      }
    });
  }
};
