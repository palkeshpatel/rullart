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
        Schema::create('apilog', function (Blueprint $table) {
            $table->integer('apiid')->primary();
            $table->string('url', 500)->nullable();
            $table->string('msg', 5000)->nullable();
            $table->date('requeston')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apilog');
    }
};