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
        Schema::create('payments_creditcard', function (Blueprint $table) {
            $table->integer('paymentid')->primary();
            $table->integer('fkcartid')->nullable();
            $table->decimal('amount', 18, 3)->nullable();
            $table->integer('fkcustomerid')->nullable();
            $table->string('resultIndicator', 50)->nullable();
            $table->string('sessionid', 50)->nullable();
            $table->string('version', 50)->nullable();
            $table->string('successIndicator', 50)->nullable();
            $table->string('sessionVersion', 50)->nullable();
            $table->date('submiton')->nullable();
            $table->string('status', 100)->nullable();
            $table->date('timeOfRecord')->nullable();
            $table->string('authorizationCode', 100)->nullable();
            $table->string('receipt', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_creditcard');
    }
};