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
        // Drop existing empty table from conflict migration if it exists
        Schema::dropIfExists('families');

        // Families - to group parents and dependents
        Schema::create('families', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // "The Smith Family"
            $table->string('primary_phone')->index(); // For fast kiosk lookup
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // Add family_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('family_id')->nullable()->after('id');
            // We'll create the index manually or it's auto-created if we used foreignId
            $table->index('family_id'); 
        });

        // Dependents (Children who are not Users yet)
        Schema::create('dependents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('family_id'); // Link to family
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('family_id')->references('id')->on('families')->onDelete('cascade');
        });

        // Locations (Rooms/Classrooms)
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Nursery (0-2)", "Pre-K", "Main Hall"
            $table->integer('capacity')->default(0); // 0 = unlimited
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Event Sessions (Specific day/time instances)
        Schema::create('event_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Sunday Service - 9AM"
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(true); // Open for check-in
            $table->integer('attendance_count')->default(0);
            $table->timestamps();
        });

        // The Check-in Record
        Schema::create('checkins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_session_id');
            $table->uuid('location_id'); // Which room they went to
            
            // Polymorphic relation to who checked in (User or Dependent)
            $table->uuid('subject_id'); 
            $table->string('subject_type'); // App\Models\User or App\Models\Dependent
            
            $table->uuid('guardian_id')->nullable(); // Who checked them in (User ID)
            $table->string('security_code', 5); // e.g., "A123"
            
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            $table->uuid('checked_out_by')->nullable();
            
            $table->enum('status', ['checked_in', 'checked_out', 'cancelled'])->default('checked_in');
            $table->timestamps();

            $table->foreign('event_session_id')->references('id')->on('event_sessions')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
        Schema::dropIfExists('event_sessions');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('dependents');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('family_id');
        });
        Schema::dropIfExists('families');
    }
};
