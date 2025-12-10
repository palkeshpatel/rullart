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
        Schema::create('stores', function (Blueprint $table) {
            $table->integer('storeid')->primary();
            $table->string('storename', 600)->nullable();
            $table->string('storenameAR', 600)->nullable();
            $table->integer('fkcountryid')->nullable();
            $table->integer('isactive')->nullable();
            $table->integer('updatedby')->nullable();
            $table->date('updateddate')->nullable();
            $table->string('website', 300)->nullable();
            $table->string('country', 300)->nullable();
            $table->string('countryAR', 300)->nullable();
            $table->string('currencycode', 30)->nullable();
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};