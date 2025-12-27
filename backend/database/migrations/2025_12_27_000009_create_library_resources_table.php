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
        Schema::create('library_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('resource_type', ['book', 'article', 'journal', 'video', 'document', 'presentation', 'link', 'other'])->default('document');
            $table->enum('access_level', ['public', 'students', 'faculty', 'specific_course', 'specific_faculty'])->default('public');
            $table->string('file_url')->nullable();
            $table->string('file_type')->nullable()->comment('file type like pdf, docx, mp4, pptx, etc.');
            $table->integer('file_size')->nullable()->comment('in KB');
            $table->string('external_link')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->string('doi')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('tags')->nullable()->comment('comma-separated tags');
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('course_id');
            $table->index('faculty_id');
            $table->index('created_by');
            $table->index('resource_type');
            $table->index('access_level');
            $table->index('is_published');
            $table->index('published_at');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_resources');
    }
};