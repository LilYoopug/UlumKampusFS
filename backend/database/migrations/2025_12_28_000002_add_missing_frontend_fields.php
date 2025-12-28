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
        Schema::table('course_modules', function (Blueprint $table) {
            $table->string('duration', 50)->nullable()->after('document_url');
            $table->string('captions_url', 500)->nullable()->after('duration');
            $table->string('attachment_url', 500)->nullable()->after('captions_url');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->json('link')->nullable()->after('action_url');
            $table->json('context')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropColumn(['duration', 'captions_url', 'attachment_url']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['link', 'context']);
        });
    }
};