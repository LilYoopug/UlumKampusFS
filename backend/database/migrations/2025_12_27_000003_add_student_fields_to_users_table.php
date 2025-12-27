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
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->onDelete('set null')->after('role');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null')->after('faculty_id');
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
            $table->dropForeign(['faculty_id']);
            $table->dropForeign(['major_id']);
            $table->dropIndex(['faculty_id']);
            $table->dropIndex(['major_id']);
            $table->dropColumn([
                'faculty_id',
                'major_id',
                'student_id',
                'gpa',
                'enrollment_year',
                'graduation_year',
                'phone',
                'address',
            ]);
        });
    }
};