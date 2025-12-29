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
        Schema::table('course_enrollments', function (Blueprint $table) {
            // Add progress tracking fields
            $table->integer('progress_percentage')->default(0)->after('status');
            $table->integer('completed_modules')->default(0)->after('progress_percentage');
            $table->integer('total_modules')->default(0)->after('completed_modules');
            
            // Add enrollment_date for additional tracking
            $table->timestamp('enrollment_date')->nullable()->after('student_id');
            
            // Indexes for better query performance
            $table->index('progress_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex(['progress_percentage']);
            $table->dropColumn(['progress_percentage', 'completed_modules', 'total_modules', 'enrollment_date']);
        });
    }
};
