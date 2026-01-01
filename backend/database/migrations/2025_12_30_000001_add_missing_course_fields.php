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
        Schema::table('courses', function (Blueprint $table) {
            // Add mode field (Live, VOD, Hybrid)
            if (!Schema::hasColumn('courses', 'mode')) {
                $table->string('mode')->nullable()->after('is_active');
            }

            // Add status field (Published, Draft, Archived) - separate from is_active
            if (!Schema::hasColumn('courses', 'status')) {
                $table->string('status')->default('Draft')->after('mode');
            }

            // Add image_url field
            if (!Schema::hasColumn('courses', 'image_url')) {
                $table->string('image_url')->nullable()->after('status');
            }

            // Add instructor_avatar_url field
            if (!Schema::hasColumn('courses', 'instructor_avatar_url')) {
                $table->string('instructor_avatar_url')->nullable()->after('image_url');
            }

            // Add learning_objectives field (JSON array)
            if (!Schema::hasColumn('courses', 'learning_objectives')) {
                $table->json('learning_objectives')->nullable()->after('instructor_avatar_url');
            }

            // Add syllabus_data field (JSON array) - using syllabus_data to avoid conflict with Laravel's syllabus
            if (!Schema::hasColumn('courses', 'syllabus_data')) {
                $table->json('syllabus_data')->nullable()->after('learning_objectives');
            }

            // Add instructor_bio_key field
            if (!Schema::hasColumn('courses', 'instructor_bio_key')) {
                $table->string('instructor_bio_key')->nullable()->after('syllabus_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'mode',
                'status',
                'image_url',
                'instructor_avatar_url',
                'learning_objectives',
                'syllabus_data',
                'instructor_bio_key'
            ]);
        });
    }
};
