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
        Schema::create('customercoupon', function (Blueprint $table) {
            $table->integer('customercouponid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->string('couponcode', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('expirydate')->nullable();
            $table->date('createdon')->nullable();
            $table->integer('createdby')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customercoupon');
    }
};