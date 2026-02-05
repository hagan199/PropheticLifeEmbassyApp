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
        Schema::create('attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('service_type', ['Sunday', 'Wednesday', 'Friday', 'Special'])->default('Sunday');
            $table->date('service_date');
            $table->integer('count')->default(1);
            $table->enum('status', ['pending', 'approved', 'rejected', 'needs_revision'])->default('pending');
            $table->foreignUuid('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('resubmitted_from')->nullable(); // Just the column, no constraint yet
            $table->timestamps();

            // Unique constraint: same member cannot be marked twice for same service/date
            $table->unique(['member_id', 'service_type', 'service_date'], 'unique_attendance');
        });

        // Add self-referencing foreign key after table is created
        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('resubmitted_from')
                ->references('id')
                ->on('attendance')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
