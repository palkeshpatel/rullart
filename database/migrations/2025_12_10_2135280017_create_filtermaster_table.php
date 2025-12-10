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
        Schema::create('filtermaster', function (Blueprint $table) {
            $table->integer('filterid')->primary();
            $table->string('filtername', 200)->nullable();
            $table->string('filternameAR', 200)->nullable();
            $table->string('filtercode', 200)->nullable();
            $table->integer('isactive')->nullable();
            $table->integer('updatedby')->nullable();
            $table->date('updateddate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filtermaster');
    }
};