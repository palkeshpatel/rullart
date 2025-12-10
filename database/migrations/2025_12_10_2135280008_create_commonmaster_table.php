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
        Schema::create('commonmaster', function (Blueprint $table) {
            $table->integer('commonid')->primary();
            $table->string('commonname', 100)->nullable();
            $table->string('commonvalue', 100)->nullable();
            $table->string('commonvalueAR', 200)->nullable();
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commonmaster');
    }
};