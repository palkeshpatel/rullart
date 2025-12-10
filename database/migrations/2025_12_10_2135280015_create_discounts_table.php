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
        Schema::create('discounts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->decimal('rate', 18, 2)->nullable();
            $table->date('startdate')->nullable();
            $table->integer('days')->nullable();
            $table->date('enddate')->nullable();
            $table->boolean('isactive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};