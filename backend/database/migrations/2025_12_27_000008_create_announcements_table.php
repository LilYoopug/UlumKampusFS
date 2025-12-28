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
        Schema::create('announcements', function (Blueprint $table) {
            $table->string('id')->primary(); // Changed from auto-increment to string ID to match seeder
            $table->string('course_id')->nullable();
            $table->string('faculty_id')->nullable();
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('category', ['general', 'academic', 'event', 'emergency', 'policy', 'exam', 'holiday'])->default('general');
            $table->enum('target_audience', ['everyone', 'students', 'faculty', 'staff', 'specific_course', 'specific_faculty'])->default('everyone');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('allow_comments')->default(false);
            $table->integer('view_count')->default(0);
            $table->string('attachment_url')->nullable();
            $table->string('attachment_type')->nullable()->comment('file type like pdf, docx, etc.');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('course_id');
            $table->index('faculty_id');
            $table->index('created_by');
            $table->index('category');
            $table->index('target_audience');
            $table->index('priority');
            $table->index('is_published');
            $table->index('published_at');
            $table->index('expires_at');
            $table->index('order');
        });

        // Add the course_id foreign key constraint separately to avoid SQLite issues with nullable values
        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};