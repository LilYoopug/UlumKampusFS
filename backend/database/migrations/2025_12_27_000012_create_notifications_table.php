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
        Schema::create('notifications', function (Blueprint $table) {
            $table->string('id')->primary(); // Changed from auto-increment to string ID to match seeder
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['announcement', 'assignment', 'discussion', 'forum', 'grade', 'course', 'system', 'reminder', 'deadline'])->default('system');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('action_url')->nullable()->comment('link to related content');
            $table->string('related_entity_type')->nullable()->comment('polymorphic: Assignment, DiscussionPost, etc.');
            $table->unsignedBigInteger('related_entity_id')->nullable()->comment('polymorphic id');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_sent')->default(false)->comment('email/push sent status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('type');
            $table->index('is_read');
            $table->index('read_at');
            $table->index('priority');
            $table->index('created_at');
            $table->index('expires_at');
            $table->index(['related_entity_type', 'related_entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};