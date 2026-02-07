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
        Schema::create('minister_unit_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('unit_name'); // e.g., "Choir", "Ushers", "Sunday School", "Media Team"
            $table->enum('service_type', ['Sunday', 'Wednesday', 'Friday', 'Special', 'Other'])->default('Sunday');
            $table->date('service_date');
            $table->time('service_time')->nullable();
            $table->foreignUuid('member_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('member_name'); // Denormalized for flexibility
            $table->boolean('present')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignUuid('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at')->useCurrent();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Flexible for custom fields per ministry
            $table->timestamps();

            // Indexes for performance
            $table->index(['service_date', 'unit_name']);
            $table->index(['department_id', 'service_date']);
            $table->index('status');
            $table->index('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minister_unit_attendance');
    }
};
