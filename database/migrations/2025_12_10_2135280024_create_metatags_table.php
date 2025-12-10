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
        Schema::create('metatags', function (Blueprint $table) {
            $table->integer('metatagsid')->primary();
            $table->string('metatitle', 400)->nullable();
            $table->string('metadescr', 800)->nullable();
            $table->string('metakeyword', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->integer('updatedby')->nullable();
            $table->date('updateddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metatags');
    }
};