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
        Schema::create('payments_qpay', function (Blueprint $table) {
            $table->integer('qpayid')->primary();
            $table->string('status', 100)->nullable();
            $table->string('referenceId', 100)->nullable();
            $table->string('transactionId', 100)->nullable();
            $table->decimal('amount', 18, 3)->nullable();
            $table->string('reason', 100)->nullable();
            $table->string('cardtype', 100)->nullable();
            $table->date('trandatetime')->nullable();
            $table->date('submiton')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_qpay');
    }
};