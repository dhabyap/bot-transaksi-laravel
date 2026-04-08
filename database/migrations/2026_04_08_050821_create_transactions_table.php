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
        Schema::create('transactions', function (Blueprint $user) {
            $user->id();
            $user->string('telegram_user_id')->index();
            $user->string('type'); // income, expense
            $user->decimal('amount', 15, 2);
            $user->string('category');
            $user->text('description')->nullable();
            $user->json('metadata')->nullable(); // Store AI details like _ai_driver
            $user->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
