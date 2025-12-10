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
        Schema::create('payments_tabby', function (Blueprint $table) {
            $table->integer('tabbyid')->primary();
            $table->string('status', 50)->nullable();
            $table->integer('reference')->nullable();
            $table->string('paymentid', 100)->nullable();
            $table->date('created_at')->nullable();
            $table->integer('is_test')->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->string('currency', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_tabby');
    }
};