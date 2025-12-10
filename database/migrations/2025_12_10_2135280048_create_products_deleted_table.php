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
        Schema::create('products_deleted', function (Blueprint $table) {
            $table->integer('productid')->primary();
            $table->integer('fkcategoryid')->nullable();
            $table->string('title', 150)->nullable();
            $table->string('titleAR', 200)->nullable();
            $table->string('productcode', 150)->nullable();
            $table->string('shortdescr', 1800)->nullable();
            $table->string('shortdescrAR', 1800)->nullable();
            $table->longText('longdescr')->nullable();
            $table->longText('longdescrAR')->nullable();
            $table->decimal('price', 18, 3)->nullable();
            $table->decimal('discount', 18, 2)->nullable();
            $table->decimal('sellingprice', 18, 3)->nullable();
            $table->string('metakeyword', 1000)->nullable();
            $table->string('metadescr', 1500)->nullable();
            $table->string('metatitle', 500)->nullable();
            $table->integer('ispublished')->nullable();
            $table->integer('isnew')->nullable()->default(0);
            $table->integer('ispopular')->nullable();
            $table->date('updateddate')->nullable();
            $table->integer('updatedby')->nullable()->default(1);
            $table->string('photo1', 100)->nullable();
            $table->string('photo2', 100)->nullable();
            $table->string('photo3', 100)->nullable();
            $table->string('photo4', 100)->nullable();
            $table->string('photo5', 100)->nullable();
            $table->string('productcode_old', 150)->nullable();
            $table->integer('internation_ship')->nullable()->default(0);
            $table->integer('productcategoryid')->nullable();
            $table->integer('productcategoryid2')->nullable();
            $table->integer('isgift')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_deleted');
    }
};