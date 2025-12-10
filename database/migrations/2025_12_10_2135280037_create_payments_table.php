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
        Schema::create('payments', function (Blueprint $table) {
            $table->integer('payid')->primary();
            $table->bigInteger('paymentid')->nullable();
            $table->string('result', 800)->nullable();
            $table->string('postdate', 500)->nullable();
            $table->string('tranid', 100)->nullable();
            $table->string('auth', 100)->nullable();
            $table->string('ref', 100)->nullable();
            $table->string('trackid', 100)->nullable();
            $table->string('udf1', 500)->nullable();
            $table->string('udf2', 500)->nullable();
            $table->string('udf3', 500)->nullable();
            $table->string('udf4', 500)->nullable();
            $table->string('udf5', 500)->nullable();
            $table->dateTime('submiton')->nullable()->useCurrent();
            $table->integer('fkcustomerid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};