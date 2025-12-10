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
        Schema::create('newsletteremails', function (Blueprint $table) {
            $table->integer('emailid')->primary();
            $table->integer('fknewsletterid')->nullable();
            $table->string('email', 200)->nullable();
            $table->integer('status')->default(0);
            $table->date('sendon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletteremails');
    }
};