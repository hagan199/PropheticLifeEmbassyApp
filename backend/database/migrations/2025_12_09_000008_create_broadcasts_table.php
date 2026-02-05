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
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recipient_group'); // all_members, partnerships, department name
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->enum('channel', ['whatsapp', 'sms'])->default('whatsapp');
            $table->text('message');
            $table->integer('total_recipients')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->decimal('delivery_rate', 5, 2)->default(0); // Percentage
            $table->enum('status', ['pending', 'sent', 'partially_sent', 'failed', 'scheduled'])->default('pending');
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->foreignUuid('sender_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
