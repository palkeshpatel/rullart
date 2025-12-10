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
        Schema::create('ordermaster', function (Blueprint $table) {
            $table->integer('orderid')->primary();
            $table->integer('fkcustomerid');
            $table->integer('fkstoreid')->default(1);
            $table->timestamp('orderdate')->useCurrent();
            $table->decimal('itemtotal', 18, 3);
            $table->decimal('shipping_charge', 18, 3)->nullable();
            $table->decimal('total', 18, 3);
            $table->integer('fkorderstatus');
            $table->string('paymentmethod', 20);
            $table->string('firstname', 50);
            $table->string('lastname', 60)->nullable();
            $table->string('mobile', 20);
            $table->string('country', 50);
            $table->string('areaname', 50);
            $table->string('address', 200)->nullable();
            $table->string('currencycode', 5);
            $table->decimal('currencyrate', 10, 6);
            $table->string('mobiledevice', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->boolean('ismobile')->default(0);
            $table->string('couponcode', 100)->nullable();
            $table->decimal('couponvalue', 18, 3)->nullable();
            $table->decimal('discount', 18, 3)->nullable();
            $table->boolean('isread')->default(0);
            $table->string('shipping_method', 100)->nullable();
            $table->decimal('vat_percent', 18, 2)->default(0);
            $table->decimal('vat', 18, 3)->default(0);
            $table->string('trackingno', 50)->nullable();
            $table->string('trackingphoto', 500)->nullable();
            $table->timestamps();
            $table->index('fkcustomerid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordermaster');
    }
};
