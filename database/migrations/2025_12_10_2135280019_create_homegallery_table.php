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
        Schema::create('homegallery', function (Blueprint $table) {
            $table->integer('homegalleryid')->primary();
            $table->string('title', 250)->nullable();
            $table->string('descr', 5000)->nullable();
            $table->string('titleAR', 500)->nullable();
            $table->string('descrAR', 1500)->nullable();
            $table->string('link', 500)->nullable();
            $table->string('photo', 250)->nullable();
            $table->string('photo_mobile', 200)->nullable();
            $table->string('photo_ar', 250)->nullable();
            $table->string('photo_mobile_ar', 250)->nullable();
            $table->date('updateddate')->nullable();
            $table->integer('updatedby')->nullable();
            $table->integer('displayorder')->nullable();
            $table->integer('ispublished')->nullable();
            $table->string('videourl', 500)->nullable()->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homegallery');
    }
};