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
        // Create payment methods table
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_id')->unique(); // bank_transfer, credit_card, e_wallet, virtual_account
            $table->string('name_key'); // translation key
            $table->text('icon')->nullable(); // SVG icon as text
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create payment items table
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_id')->unique(); // registration, semester, exam
            $table->string('title_key')->nullable(); // translation key (legacy)
            $table->string('description_key')->nullable(); // translation key (legacy)
            $table->string('title')->nullable(); // actual title in Indonesian
            $table->text('description')->nullable(); // actual description in Indonesian
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['paid', 'unpaid', 'pending'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Create payment histories table
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->string('history_id')->unique();
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->timestamp('payment_date')->nullable();
            $table->enum('status', ['completed', 'failed', 'pending'])->default('pending');
            $table->string('payment_method_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Add foreign key constraint manually to reference method_id instead of id
        Schema::table('payment_histories', function (Blueprint $table) {
            $table->foreign('payment_method_id')->references('method_id')->on('payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payment_methods');
    }
};
