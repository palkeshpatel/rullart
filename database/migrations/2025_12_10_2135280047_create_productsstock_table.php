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
        Schema::create('productsstock', function (Blueprint $table) {
            $table->integer('stockid')->primary();
            $table->integer('fkproductid')->nullable();
            $table->integer('fkproductfilterid')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('prevqty')->nullable();
            $table->string('action', 500)->nullable();
            $table->dateTime('updateddate')->nullable()->useCurrent();
            $table->integer('updatedby')->nullable();
            $table->integer('fkorderid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productsstock');
    }
};