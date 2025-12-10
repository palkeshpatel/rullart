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
        Schema::create('payments_applepay', function (Blueprint $table) {
            $table->integer('paymentid')->primary();
            $table->string('fkcartid', 255)->nullable();
            $table->string('amount', 255)->nullable();
            $table->string('fkcustomerid', 255)->nullable();
            $table->text('resultIndicator')->nullable();
            $table->string('sessionVersion', 255)->nullable();
            $table->date('submiton')->nullable();
            $table->longText('response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_applepay');
    }
};