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
        Schema::create('pages_mobile', function (Blueprint $table) {
            $table->integer('pageid')->primary();
            $table->string('pagetitle', 200)->nullable();
            $table->string('pagetitleAR', 200)->nullable();
            $table->string('pagename', 200)->nullable();
            $table->string('photo', 500)->nullable();
            $table->longText('details')->nullable();
            $table->longText('detailsAR')->nullable();
            $table->string('metatitle', 200)->nullable();
            $table->string('metakeyword', 500)->nullable();
            $table->string('metadescription', 1500)->nullable();
            $table->date('updateddate')->nullable();
            $table->integer('fkuserid')->nullable();
            $table->integer('published')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages_mobile');
    }
};