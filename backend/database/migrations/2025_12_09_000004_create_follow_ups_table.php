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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('visitor_id')->constrained('visitors')->onDelete('cascade');
            $table->enum('contact_method', ['WhatsApp', 'SMS', 'Call', 'In-person'])->default('Call');
            $table->text('outcome_notes');
            $table->enum('status_after', ['not_contacted', 'contacted', 'engaged', 'converted']);
            $table->date('next_follow_up_date')->nullable();
            $table->foreignUuid('logged_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
