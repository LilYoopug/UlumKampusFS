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
        Schema::table('courses', function (Blueprint $table) {
            // Only add fields that don't already exist
            if (!Schema::hasColumn('courses', 'learning_objectives')) {
                $table->json('learning_objectives')->nullable()->after('description');
            }
            if (!Schema::hasColumn('courses', 'syllabus')) {
                $table->json('syllabus')->nullable()->after('learning_objectives');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['learning_objectives', 'syllabus']);
        });
    }
};
