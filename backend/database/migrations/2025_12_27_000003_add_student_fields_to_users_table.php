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
        Schema::table('users', function (Blueprint $table) {
            $table->string('faculty_id')->nullable()->after('role');
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('set null');
            $table->string('major_id')->nullable()->after('faculty_id');
            $table->foreign('major_id')->references('code')->on('majors')->onDelete('set null');
            $table->string('student_id')->nullable()->unique()->after('major_id');
            $table->decimal('gpa', 3, 2)->nullable()->after('student_id');
            $table->integer('enrollment_year')->nullable()->after('gpa');
            $table->integer('graduation_year')->nullable()->after('enrollment_year');
            $table->string('phone')->nullable()->after('graduation_year');
            $table->text('address')->nullable()->after('phone');

            $table->index('faculty_id');
            $table->index('major_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // For SQLite, we'll drop the columns directly without explicitly dropping foreign keys
            // Laravel handles this automatically in most cases
            $table->dropColumn([
                'faculty_id',
                'major_id',
                'student_id',
                'gpa',
                'enrollment_year',
                'graduation_year',
                'phone',
                'address'
            ]);
        });
    }
};