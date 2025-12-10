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
        Schema::create('shoppingcartmaster', function (Blueprint $table) {
            $table->integer('cartid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->integer('fkstoreid')->default(1);
            $table->string('sessionid', 50)->nullable();
            $table->dateTime('orderdate')->nullable()->useCurrent();
            $table->decimal('itemtotal', 18, 3)->nullable();
            $table->decimal('shipping_charge', 18, 3)->nullable();
            $table->decimal('giftbox_charge', 18, 3)->nullable()->default(0);
            $table->decimal('total', 18, 3)->nullable();
            $table->string('paymentmethod', 20)->nullable();
            $table->integer('addressid')->nullable();
            $table->string('lang', 5)->nullable();
            $table->integer('asGift')->default(0);
            $table->string('giftMessage', 1500)->nullable();
            $table->integer('billingaddressid')->nullable()->default(0);
            $table->integer('shippingaddressid')->nullable()->default(0);
            $table->string('couponcode', 100)->nullable();
            $table->decimal('couponvalue', 18, 2)->nullable();
            $table->decimal('discount', 18, 2)->nullable();
            $table->integer('shippingcountryid')->nullable();
            $table->string('shipping_method', 100)->nullable();
            $table->integer('phaseid')->nullable();
            $table->string('additionalinstruction', 2000)->nullable();
            $table->string('phase', 100)->nullable();
            $table->string('phaseAR', 100)->nullable();
            $table->integer('phaseidBill')->nullable();
            $table->string('additionalinstructionBill', 2000)->nullable();
            $table->string('delivery_methodBill', 100)->nullable();
            $table->string('phaseBill', 100)->nullable();
            $table->string('phaseARBill', 100)->nullable();
            $table->string('delivery_method', 100)->nullable();
            $table->decimal('vat_percent', 18, 2)->nullable()->default(0);
            $table->decimal('vat', 18, 3)->nullable()->default(0);
            $table->decimal('free_shipping_over', 18, 3)->nullable();
            $table->string('free_shipping_text', 200)->nullable();
            $table->integer('ismobile')->nullable()->default(0);
            $table->string('mobiledevice', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('platform', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoppingcartmaster');
    }
};