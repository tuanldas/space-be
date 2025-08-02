<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->uuid('category_id');
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('amount', 11, 2);
            $table->timestamp('transaction_date');
            $table->string('transaction_type');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('wallet_id')->references('id')->on('wallets');
            $table->foreign('category_id')->references('id')->on('transaction_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
