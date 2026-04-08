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
        // 1. Rename columns first
        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('telegram_user_id', 'user_id');
            $table->renameColumn('type', 'tipe');
            $table->renameColumn('amount', 'nominal');
            $table->renameColumn('category', 'kategori');
            $table->renameColumn('description', 'item');
        });

        // 2. Change column type and add new ones
        Schema::table('transactions', function (Blueprint $table) {
            $table->double('nominal')->change();

            if (!Schema::hasColumn('transactions', 'timestamp')) {
                $table->dateTime('timestamp')->nullable()->after('kategori');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'timestamp')) {
                $table->dropColumn('timestamp');
            }
            $table->decimal('nominal', 15, 2)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'telegram_user_id');
            $table->renameColumn('tipe', 'type');
            $table->renameColumn('nominal', 'amount');
            $table->renameColumn('kategori', 'category');
            $table->renameColumn('item', 'description');
        });
    }
};
