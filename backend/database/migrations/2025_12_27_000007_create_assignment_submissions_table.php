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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable()->comment('in bytes');
            $table->string('link_url')->nullable();
            $table->enum('status', ['draft', 'submitted', 'late', 'graded', 'returned'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('grade', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->boolean('is_late')->default(false);
            $table->timestamp('late_submission_at')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id', 'attempt_number']);
            $table->index('assignment_id');
            $table->index('student_id');
            $table->index('graded_by');
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};