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
        Schema::create('customers_devices', function (Blueprint $table) {
            $table->integer('deviceid')->primary();
            $table->string('device_uid', 500)->nullable();
            $table->integer('fkcustomerid')->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('device_version', 100)->nullable();
            $table->string('device_otherdetails', 2000)->nullable();
            $table->date('update_date')->nullable();
            $table->integer('badge')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_devices');
    }
};