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
            // Add missing User fields
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'student_status')) {
                $table->enum('student_status', ['Aktif', 'Cuti', 'Lulus', 'DO', 'Pendaftaran'])->default('Aktif')->nullable();
            }
            if (!Schema::hasColumn('users', 'total_sks')) {
                $table->integer('total_sks')->default(0)->nullable();
            }
            if (!Schema::hasColumn('users', 'badges')) {
                $table->json('badges')->nullable();
            }
        });

        Schema::table('courses', function (Blueprint $table) {
            // Add missing Course fields
            if (!Schema::hasColumn('courses', 'mode')) {
                $table->string('mode')->nullable()->comment('Live | VOD');
            }
            if (!Schema::hasColumn('courses', 'status')) {
                $table->enum('status', ['Published', 'Draft', 'Archived'])->default('Draft')->nullable();
            }
            if (!Schema::hasColumn('courses', 'image_url')) {
                $table->string('image_url')->nullable();
            }
            if (!Schema::hasColumn('courses', 'instructor_avatar_url')) {
                $table->string('instructor_avatar_url')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'bio', 'student_status', 'total_sks', 'badges']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['mode', 'status', 'image_url', 'instructor_avatar_url']);
        });
    }
};