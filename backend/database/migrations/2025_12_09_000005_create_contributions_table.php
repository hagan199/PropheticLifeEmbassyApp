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
        Schema::create('contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('member_id')->nullable()->constrained('users')->onDelete('set null'); // Nullable for anonymous
            $table->enum('type', ['tithe', 'offering', 'special_seed', 'building_fund', 'missions', 'welfare'])->default('offering');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'momo', 'bank', 'cheque'])->default('cash');
            $table->string('reference')->nullable(); // Transaction reference
            $table->string('mobile_number')->nullable(); // For mobile money
            $table->date('date');
            $table->date('contribution_month')->nullable(); // For partnership tracking
            $table->enum('frequency', ['weekly', 'monthly', 'as_able'])->nullable(); // For partnerships
            $table->date('expected_date')->nullable();
            $table->enum('status', ['pending_review', 'reviewed', 'approved', 'rejected', 'overdue'])->default('pending_review');
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
