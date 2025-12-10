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
        Schema::create('cartmaster', function (Blueprint $table) {
            $table->integer('cartid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->integer('fkstoreid')->default(1);
            $table->dateTime('orderdate')->nullable()->useCurrent();
            $table->decimal('itemtotal', 18, 3)->nullable();
            $table->decimal('shipping_charge', 18, 3)->nullable();
            $table->decimal('total', 18, 3)->nullable();
            $table->integer('fkorderstatus')->nullable();
            $table->string('paymentmethod', 20)->nullable();
            $table->integer('addressid')->nullable();
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('title', 50)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('areaname', 50)->nullable();
            $table->string('address', 200)->nullable();
            $table->string('firstnameBill', 50)->nullable();
            $table->string('lastnameBill', 50)->nullable();
            $table->string('mobileBill', 50)->nullable();
            $table->string('countryBill', 50)->nullable();
            $table->string('areanameBill', 50)->nullable();
            $table->string('addressBill', 200)->nullable();
            $table->integer('payid')->nullable();
            $table->integer('paymentid')->nullable();
            $table->integer('trackid')->nullable();
            $table->string('lang', 5)->nullable();
            $table->integer('asGift')->default(0);
            $table->string('giftMessage', 1500)->nullable();
            $table->string('successIndicator', 50)->nullable();
            $table->string('city', 155)->nullable();
            $table->string('block_number', 200)->nullable();
            $table->string('house_number', 200)->nullable();
            $table->string('avenue_number', 200)->nullable();
            $table->string('street_number', 200)->nullable();
            $table->string('cityBill', 155)->nullable();
            $table->string('block_numberBill', 200)->nullable();
            $table->string('house_numberBill', 200)->nullable();
            $table->string('avenue_numberBill', 200)->nullable();
            $table->string('street_numberBill', 200)->nullable();
            $table->string('securityid', 155)->nullable();
            $table->string('building_number', 50)->nullable();
            $table->string('floor_number', 50)->nullable();
            $table->string('flat_number', 50)->nullable();
            $table->string('building_numberBill', 50)->nullable();
            $table->string('floor_numberBill', 50)->nullable();
            $table->string('flat_numberBill', 50)->nullable();
            $table->string('currencycode', 5)->nullable();
            $table->decimal('currencyrate', 10, 6)->nullable();
            $table->integer('remindersend')->nullable()->default(0);
            $table->string('couponcode', 100)->nullable();
            $table->decimal('couponvalue', 18, 3)->nullable();
            $table->decimal('discount', 18, 3)->nullable();
            $table->string('shipping_method', 100)->nullable();
            $table->integer('phaseid')->nullable();
            $table->string('additionalinstruction', 2000)->nullable();
            $table->string('delivery_method', 100)->nullable();
            $table->decimal('vat_percent', 18, 2)->nullable()->default(0);
            $table->decimal('vat', 18, 3)->nullable()->default(0);
            $table->string('phase', 100)->nullable();
            $table->string('phaseAR', 100)->nullable();
            $table->integer('phaseidBill')->nullable();
            $table->string('additionalinstructionBill', 2000)->nullable();
            $table->string('delivery_methodBill', 100)->nullable();
            $table->string('phaseBill', 100)->nullable();
            $table->string('phaseARBill', 100)->nullable();
            $table->integer('ismobile')->nullable();
            $table->string('appversion', 500)->nullable();
            $table->string('mobiledevice', 100)->nullable();
            $table->string('token', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
            $table->integer('emailcount')->nullable()->default(0);
            $table->date('emailsenddate')->nullable();
            $table->date('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cartmaster');
    }
};