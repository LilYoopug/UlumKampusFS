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
        Schema::table('grades', function (Blueprint $table) {
            // Add student_id as an alias to user_id
            $table->foreignId('student_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Add graded_by for instructor reference
            $table->foreignId('graded_by')->nullable()->after('assignment_id')->constrained('users')->onDelete('set null');
            
            // Add score field (numeric score)
            $table->decimal('score', 8, 2)->nullable()->after('grade');
            
            // Add grade_numeric field
            $table->integer('grade_numeric')->nullable()->after('grade_letter');
            
            // Add feedback field
            $table->text('feedback')->nullable()->after('grade_numeric');
            
            // Add graded_at timestamp
            $table->timestamp('graded_at')->nullable()->after('feedback');
            
            // Indexes
            $table->index('student_id');
            $table->index('graded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['graded_by']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['graded_by']);
            $table->dropColumn(['student_id', 'graded_by', 'score', 'grade_numeric', 'feedback', 'graded_at']);
        });
    }
};
