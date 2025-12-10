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
        Schema::create('pay', function (Blueprint $table) {
            $table->integer('payid')->primary();
            $table->string('firstname', 100)->nullable();
            $table->string('lastname', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('orderid')->nullable();
            $table->decimal('amount', 18, 3)->nullable();
            $table->date('paydate')->nullable();
            $table->integer('paystatus')->nullable();
            $table->string('transid', 100)->nullable();
            $table->integer('paymentid')->nullable();
            $table->dateTime('submiton')->nullable()->useCurrent();
            $table->string('successIndicator', 50)->nullable();
            $table->string('resultIndicator', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay');
    }
};