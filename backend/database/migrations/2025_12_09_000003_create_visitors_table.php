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
        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('category', ['Visitor', 'Partner'])->default('Visitor');
            $table->enum('source', ['Friend', 'Social Media', 'Walk-in', 'Other'])->nullable();
            $table->string('source_detail')->nullable(); // If source is 'Other'
            $table->json('ministry_interest')->nullable(); // Array of interests: Youth, Counseling, Giving, Fellowship, Other
            $table->text('initial_notes')->nullable();
            $table->date('first_visit_date');
            $table->enum('status', ['not_contacted', 'contacted', 'engaged', 'converted'])->default('not_contacted');
            $table->date('next_follow_up_date')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
