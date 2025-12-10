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
        Schema::create('productsfilter_deleted', function (Blueprint $table) {
            $table->integer('fkproductid')->primary();
            $table->integer('fkfiltervalueid')->nullable();
            $table->string('filtercode', 50)->nullable();
            $table->integer('qty')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productsfilter_deleted');
    }
};