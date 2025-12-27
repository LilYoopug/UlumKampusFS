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
        Schema::create('discussion_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('discussion_threads')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_solution')->default(false)->comment('marks post as solution to the thread');
            $table->foreignId('marked_as_solution_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('marked_as_solution_at')->nullable();
            $table->integer('likes_count')->default(0);
            $table->string('attachment_url')->nullable();
            $table->string('attachment_type')->nullable()->comment('file type like pdf, docx, etc.');
            $table->timestamps();
            $table->softDeletes();

            $table->index('thread_id');
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('edited_by');
            $table->index('marked_as_solution_by');
            $table->index('is_solution');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_posts');
    }
};