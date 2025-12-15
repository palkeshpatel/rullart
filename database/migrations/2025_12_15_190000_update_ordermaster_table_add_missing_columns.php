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
        Schema::table('ordermaster', function (Blueprint $table) {
            // Add missing columns from CI database
            if (!Schema::hasColumn('ordermaster', 'addressid')) {
                $table->integer('addressid')->after('paymentmethod');
            }
            if (!Schema::hasColumn('ordermaster', 'title')) {
                $table->string('title', 50)->after('lastname');
            }
            if (!Schema::hasColumn('ordermaster', 'firstnameBill')) {
                $table->string('firstnameBill', 50)->after('address');
            }
            if (!Schema::hasColumn('ordermaster', 'lastnameBill')) {
                $table->string('lastnameBill', 60)->nullable()->after('firstnameBill');
            }
            if (!Schema::hasColumn('ordermaster', 'mobileBill')) {
                $table->string('mobileBill', 50)->after('lastnameBill');
            }
            if (!Schema::hasColumn('ordermaster', 'countryBill')) {
                $table->string('countryBill', 50)->after('mobileBill');
            }
            if (!Schema::hasColumn('ordermaster', 'areanameBill')) {
                $table->string('areanameBill', 50)->after('countryBill');
            }
            if (!Schema::hasColumn('ordermaster', 'addressBill')) {
                $table->string('addressBill', 200)->after('areanameBill');
            }
            if (!Schema::hasColumn('ordermaster', 'payid')) {
                $table->integer('payid')->nullable()->after('addressBill');
            }
            if (!Schema::hasColumn('ordermaster', 'paymentid')) {
                $table->string('paymentid', 100)->nullable()->after('payid');
            }
            if (!Schema::hasColumn('ordermaster', 'tranid')) {
                $table->string('tranid', 100)->nullable()->after('paymentid');
            }
            if (!Schema::hasColumn('ordermaster', 'lang')) {
                $table->string('lang', 5)->nullable()->after('tranid');
            }
            if (!Schema::hasColumn('ordermaster', 'asGift')) {
                $table->boolean('asGift')->default(0)->after('lang');
            }
            if (!Schema::hasColumn('ordermaster', 'giftMessage')) {
                $table->string('giftMessage', 1500)->nullable()->after('asGift');
            }
            if (!Schema::hasColumn('ordermaster', 'successIndicator')) {
                $table->string('successIndicator', 50)->nullable()->after('giftMessage');
            }
            if (!Schema::hasColumn('ordermaster', 'city')) {
                $table->string('city', 155)->nullable()->after('successIndicator');
            }
            if (!Schema::hasColumn('ordermaster', 'block_number')) {
                $table->string('block_number', 200)->nullable()->after('city');
            }
            if (!Schema::hasColumn('ordermaster', 'house_number')) {
                $table->string('house_number', 200)->nullable()->after('block_number');
            }
            if (!Schema::hasColumn('ordermaster', 'avenue_number')) {
                $table->string('avenue_number', 200)->nullable()->after('house_number');
            }
            if (!Schema::hasColumn('ordermaster', 'street_number')) {
                $table->string('street_number', 200)->nullable()->after('avenue_number');
            }
            if (!Schema::hasColumn('ordermaster', 'cityBill')) {
                $table->string('cityBill', 155)->nullable()->after('street_number');
            }
            if (!Schema::hasColumn('ordermaster', 'block_numberBill')) {
                $table->string('block_numberBill', 200)->nullable()->after('cityBill');
            }
            if (!Schema::hasColumn('ordermaster', 'house_numberBill')) {
                $table->string('house_numberBill', 200)->nullable()->after('block_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'avenue_numberBill')) {
                $table->string('avenue_numberBill', 200)->nullable()->after('house_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'street_numberBill')) {
                $table->string('street_numberBill', 200)->nullable()->after('avenue_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'securityid')) {
                $table->string('securityid', 155)->nullable()->after('street_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'building_number')) {
                $table->string('building_number', 50)->nullable()->after('securityid');
            }
            if (!Schema::hasColumn('ordermaster', 'floor_number')) {
                $table->string('floor_number', 50)->nullable()->after('building_number');
            }
            if (!Schema::hasColumn('ordermaster', 'flat_number')) {
                $table->string('flat_number', 50)->nullable()->after('floor_number');
            }
            if (!Schema::hasColumn('ordermaster', 'building_numberBill')) {
                $table->string('building_numberBill', 50)->nullable()->after('flat_number');
            }
            if (!Schema::hasColumn('ordermaster', 'floor_numberBill')) {
                $table->string('floor_numberBill', 50)->nullable()->after('building_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'flat_numberBill')) {
                $table->string('flat_numberBill', 50)->nullable()->after('floor_numberBill');
            }
            if (!Schema::hasColumn('ordermaster', 'fkcartid')) {
                $table->integer('fkcartid')->default(0)->after('currencyrate');
            }
            if (!Schema::hasColumn('ordermaster', 'phaseid')) {
                $table->integer('phaseid')->nullable()->after('shipping_method');
            }
            if (!Schema::hasColumn('ordermaster', 'additionalinstruction')) {
                $table->string('additionalinstruction', 2000)->nullable()->after('phaseid');
            }
            if (!Schema::hasColumn('ordermaster', 'delivery_method')) {
                $table->string('delivery_method', 100)->nullable()->after('additionalinstruction');
            }
            if (!Schema::hasColumn('ordermaster', 'phase')) {
                $table->string('phase', 100)->nullable()->after('vat');
            }
            if (!Schema::hasColumn('ordermaster', 'phaseAR')) {
                $table->string('phaseAR', 100)->nullable()->after('phase');
            }
            if (!Schema::hasColumn('ordermaster', 'phaseidBill')) {
                $table->integer('phaseidBill')->nullable()->after('phaseAR');
            }
            if (!Schema::hasColumn('ordermaster', 'additionalinstructionBill')) {
                $table->string('additionalinstructionBill', 2000)->nullable()->after('phaseidBill');
            }
            if (!Schema::hasColumn('ordermaster', 'delivery_methodBill')) {
                $table->string('delivery_methodBill', 100)->nullable()->after('additionalinstructionBill');
            }
            if (!Schema::hasColumn('ordermaster', 'phaseBill')) {
                $table->string('phaseBill', 100)->nullable()->after('delivery_methodBill');
            }
            if (!Schema::hasColumn('ordermaster', 'phaseARBill')) {
                $table->string('phaseARBill', 100)->nullable()->after('phaseBill');
            }
            if (!Schema::hasColumn('ordermaster', 'appversion')) {
                $table->string('appversion', 500)->nullable()->after('phaseARBill');
            }
            if (!Schema::hasColumn('ordermaster', 'approvalcode')) {
                $table->string('approvalcode', 200)->nullable()->after('appversion');
            }
            if (!Schema::hasColumn('ordermaster', 'refundpun')) {
                $table->string('refundpun', 200)->nullable()->after('approvalcode');
            }
            if (!Schema::hasColumn('ordermaster', 'refundresponse')) {
                $table->string('refundresponse', 200)->nullable()->after('refundpun');
            }
            if (!Schema::hasColumn('ordermaster', 'courier_company')) {
                $table->string('courier_company', 100)->nullable()->after('trackingno');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordermaster', function (Blueprint $table) {
            $columns = [
                'addressid', 'title', 'firstnameBill', 'lastnameBill', 'mobileBill',
                'countryBill', 'areanameBill', 'addressBill', 'payid', 'paymentid',
                'tranid', 'lang', 'asGift', 'giftMessage', 'successIndicator',
                'city', 'block_number', 'house_number', 'avenue_number', 'street_number',
                'cityBill', 'block_numberBill', 'house_numberBill', 'avenue_numberBill',
                'street_numberBill', 'securityid', 'building_number', 'floor_number',
                'flat_number', 'building_numberBill', 'floor_numberBill', 'flat_numberBill',
                'fkcartid', 'phaseid', 'additionalinstruction', 'delivery_method',
                'phase', 'phaseAR', 'phaseidBill', 'additionalinstructionBill',
                'delivery_methodBill', 'phaseBill', 'phaseARBill', 'appversion',
                'approvalcode', 'refundpun', 'refundresponse', 'courier_company'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('ordermaster', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

