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
        Schema::create('filtervalues', function (Blueprint $table) {
            $table->integer('filtervalueid')->primary();
            $table->integer('fkfilterid')->nullable();
            $table->string('filtervalue', 200)->nullable();
            $table->string('filtervalueAR', 200)->nullable();
            $table->string('filtervaluecode', 200)->nullable();
            $table->integer('isactive')->nullable()->default(1);
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filtervalues');
    }
};