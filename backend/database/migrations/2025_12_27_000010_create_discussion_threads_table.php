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
        Schema::create('discussion_threads', function (Blueprint $table) {
            $table->string('id')->primary(); // Changed from auto-increment to string ID to match seeder
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('course_modules')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['question', 'discussion', 'announcement', 'help'])->default('discussion');
            $table->enum('status', ['open', 'closed', 'archived'])->default('open');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_closed')->default(false); // Added to match seeder
            $table->boolean('is_locked')->default(false);
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('closed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->foreignId('last_post_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_post_at')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('attachment_type')->nullable()->comment('file type like pdf, docx, etc.');
            $table->timestamps();
            $table->softDeletes();

            $table->index('course_id');
            $table->index('module_id');
            $table->index('created_by');
            $table->index('locked_by');
            $table->index('closed_by');
            $table->index('last_post_by');
            $table->index('type');
            $table->index('status');
            $table->index('is_pinned');
            $table->index('is_locked');
            $table->index('last_post_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_threads');
    }
};