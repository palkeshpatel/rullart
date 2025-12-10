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
        Schema::create('orderlog', function (Blueprint $table) {
            $table->integer('orderlogid')->primary();
            $table->integer('fkorderid')->nullable();
            $table->integer('fkuserid')->nullable();
            $table->integer('fkorderstatus')->nullable();
            $table->string('descr', 500)->nullable();
            $table->dateTime('actionon')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderlog');
    }
};