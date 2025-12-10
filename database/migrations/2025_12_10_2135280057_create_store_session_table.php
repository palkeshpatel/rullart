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
        Schema::create('store_session', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('session_id', 255)->nullable();
            $table->string('origin', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('device', 255)->nullable();
            $table->date('created_at')->nullable();
            $table->integer('added_to_cart')->default(0);
            $table->integer('reached_checkout')->default(0);
            $table->integer('converted')->default(0);
            $table->string('type', 255)->nullable();
            $table->string('session_type', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_session');
    }
};