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
        Schema::create('addressbook', function (Blueprint $table) {
            $table->integer('addressid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->string('title', 100)->nullable();
            $table->string('firstname', 100)->nullable();
            $table->string('lastname', 100)->nullable();
            $table->string('mobile', 25)->nullable();
            $table->string('country', 50)->nullable();
            $table->integer('fkareaid')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('securityid', 50)->nullable();
            $table->string('city', 155)->nullable();
            $table->string('block_number', 200)->nullable();
            $table->string('house_number', 200)->nullable();
            $table->string('avenue_number', 200)->nullable();
            $table->string('street_number', 200)->nullable();
            $table->string('building_number', 50)->nullable();
            $table->string('floor_number', 50)->nullable();
            $table->string('flat_number', 50)->nullable();
            $table->integer('fkcountryid')->nullable();
            $table->integer('is_default')->nullable()->default(0);
            $table->integer('phaseid')->nullable();
            $table->string('phase', 100)->nullable();
            $table->string('phaseAR', 100)->nullable();
            $table->string('additionalinstruction', 2000)->nullable();
            $table->string('delivery_method', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addressbook');
    }
};