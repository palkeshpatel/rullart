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
        Schema::create('areamaster', function (Blueprint $table) {
            $table->integer('areaid')->primary();
            $table->integer('fkcountryid')->nullable();
            $table->string('areaname', 100)->nullable();
            $table->string('areanameAR', 100)->nullable();
            $table->integer('isactive')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areamaster');
    }
};