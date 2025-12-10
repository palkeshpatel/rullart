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
        Schema::create('mantrajap_device', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('fkleadform_id')->nullable();
            $table->string('device_id', 255)->nullable();
            $table->string('os', 64)->nullable();
            $table->string('version', 64)->nullable();
            $table->string('device_name', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantrajap_device');
    }
};