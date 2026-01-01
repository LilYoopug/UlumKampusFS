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
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Informasi Pribadi (Personal Information)
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('citizenship')->default('Indonesia');
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('parent_job')->nullable();

            // Informasi Pendidikan (Education Information)
            $table->string('school_name')->nullable();
            $table->text('school_address')->nullable();
            $table->integer('graduation_year_school')->nullable();
            $table->enum('school_type', ['SMA', 'SMK', 'MA', 'Lainnya'])->nullable();
            $table->string('school_major')->nullable();
            $table->decimal('average_grade', 5, 2)->nullable();

            // Preferensi (Preferences)
            $table->string('first_choice_id')->nullable();
            $table->foreign('first_choice_id')->references('code')->on('majors')->onDelete('set null');
            $table->string('second_choice_id')->nullable();
            $table->foreign('second_choice_id')->references('code')->on('majors')->onDelete('set null');

            // Registration Status
            $table->enum('status', ['draft', 'submitted', 'under_review', 'accepted', 'rejected'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('documents')->nullable(); // JSON array of document paths
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('user_id');
            $table->index('first_choice_id');
            $table->index('second_choice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
