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
        Schema::create('occassion', function (Blueprint $table) {
            $table->integer('occassionid')->primary();
            $table->string('occassion', 150)->nullable();
            $table->string('occassionAR', 150)->nullable();
            $table->string('occassioncode', 150)->nullable();
            $table->string('photo', 200)->nullable();
            $table->integer('ispublished')->nullable();
            $table->string('metakeyword', 1000)->nullable();
            $table->string('metadescr', 1500)->nullable();
            $table->string('metatitle', 500)->nullable();
            $table->date('updateddate')->nullable();
            $table->integer('updatedby')->nullable();
            $table->integer('showhome')->nullable();
            $table->string('photo_mobile', 100)->nullable();
            $table->string('metatitleAR', 500)->nullable();
            $table->string('metadescrAR', 1000)->nullable();
            $table->string('metakeywordAR', 1000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occassion');
    }
};