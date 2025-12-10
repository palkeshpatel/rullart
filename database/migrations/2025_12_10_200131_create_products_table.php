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
        Schema::create('products', function (Blueprint $table) {
            $table->integer('productid')->primary();
            $table->integer('fkcategoryid');
            $table->string('title', 150);
            $table->string('titleAR', 200);
            $table->string('productcode', 150)->unique();
            $table->text('shortdescr');
            $table->text('shortdescrAR');
            $table->longText('longdescr');
            $table->longText('longdescrAR');
            $table->decimal('price', 18, 3);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('sellingprice', 18, 3);
            $table->string('metakeyword', 1000)->nullable();
            $table->string('metadescr', 1500)->nullable();
            $table->string('metatitle', 500);
            $table->boolean('ispublished')->default(1);
            $table->boolean('isnew')->default(0);
            $table->boolean('ispopular')->default(0);
            $table->string('photo1', 100);
            $table->string('photo2', 100)->nullable();
            $table->string('photo3', 100)->nullable();
            $table->string('photo4', 100)->nullable();
            $table->string('photo5', 100)->nullable();
            $table->boolean('isgift')->default(0);
            $table->integer('updatedby')->default(1);
            $table->timestamp('updateddate')->nullable();
            $table->timestamps();
            $table->index('fkcategoryid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
