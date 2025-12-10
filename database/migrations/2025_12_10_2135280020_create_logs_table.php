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
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('uri', 255)->nullable();
            $table->string('method', 6)->nullable();
            $table->text('params')->nullable();
            $table->string('api_key', 40)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('time')->nullable();
            $table->float('rtime')->nullable();
            $table->string('authorized', 1)->nullable();
            $table->integer('response_code')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};