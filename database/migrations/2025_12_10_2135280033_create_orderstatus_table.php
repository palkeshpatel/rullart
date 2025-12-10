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
        Schema::create('orderstatus', function (Blueprint $table) {
            $table->integer('statusid')->primary();
            $table->string('status', 100)->nullable();
            $table->string('statusAR', 50)->nullable();
            $table->string('classname', 50)->nullable();
            $table->string('classname2', 50)->nullable();
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderstatus');
    }
};