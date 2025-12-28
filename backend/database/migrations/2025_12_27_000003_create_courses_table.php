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
        Schema::create('courses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('faculty_id');
            $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->string('major_id');
            $table->foreign('major_id')->references('code')->on('majors')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('credit_hours')->default(3);
            $table->integer('capacity')->default(50);
            $table->integer('current_enrollment')->default(0);
            $table->string('semester')->default('Fall');
            $table->integer('year')->default(2024);
            $table->string('schedule')->nullable();
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('faculty_id');
            $table->index('major_id');
            $table->index('instructor_id');
            $table->index('code');
            $table->index('year');
            $table->index('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};