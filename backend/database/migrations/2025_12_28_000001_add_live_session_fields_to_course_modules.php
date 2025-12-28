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
        Schema::table('course_modules', function (Blueprint $table) {
            // Add type column for module type (video, pdf, quiz, live)
            $table->enum('type', ['video', 'pdf', 'quiz', 'live'])->default('video')->after('course_id');

            // Add start_time for live sessions
            $table->timestamp('start_time')->nullable()->after('published_at');

            // Add live_url for live session links (Zoom, Google Meet, etc.)
            $table->string('live_url')->nullable()->after('start_time');

            // Index for filtering by type
            $table->index('type');
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['start_time']);
            $table->dropColumn(['type', 'start_time', 'live_url']);
        });
    }
};