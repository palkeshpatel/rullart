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
        Schema::create('category', function (Blueprint $table) {
            $table->integer('categoryid')->primary();
            $table->string('category', 150);
            $table->string('categoryAR', 200);
            $table->string('categorycode', 150)->unique();
            $table->boolean('ispublished')->default(1);
            $table->boolean('showmenu')->default(1);
            $table->smallInteger('displayorder');
            $table->integer('parentid')->default(0);
            $table->string('metakeyword', 1000)->nullable();
            $table->string('metadescr', 1500)->nullable();
            $table->string('metatitle', 500)->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('photo_mobile', 100)->nullable();
            $table->integer('updatedby')->default(1);
            $table->timestamp('updateddate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category');
    }
};
