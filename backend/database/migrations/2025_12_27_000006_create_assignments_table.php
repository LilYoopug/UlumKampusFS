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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreignId('module_id')->nullable()->constrained('course_modules')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->decimal('max_points', 8, 2)->default(100.00);
            $table->enum('submission_type', ['text', 'file', 'link', 'mixed', 'hafalan'])->default('text');
            $table->string('allowed_file_types')->nullable();
            $table->integer('max_file_size')->nullable()->comment('in KB');
            $table->integer('attempts_allowed')->default(1);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->decimal('late_penalty', 5, 2)->default(0.00)->comment('percentage to deduct');
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('course_id');
            $table->index('module_id');
            $table->index('created_by');
            $table->index('due_date');
            $table->index('is_published');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};