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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // create, update, delete, login, logout, approve, reject, etc.
            $table->string('entity_type'); // users, attendance, contributions, expenses, etc.
            $table->string('entity_id')->nullable();
            $table->json('changes')->nullable(); // {before: {...}, after: {...}}
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable(); // Human-readable description
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('action');
            $table->index('entity_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
