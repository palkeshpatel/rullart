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
        Schema::create('countrymaster', function (Blueprint $table) {
            $table->integer('countryid')->primary();
            $table->string('countryname', 50)->nullable();
            $table->string('countrynameAR', 50)->nullable();
            $table->integer('isactive')->nullable()->default(1);
            $table->decimal('shipping_charge', 18, 2)->nullable();
            $table->string('shipping_days', 1000)->default('');
            $table->string('shipping_daysAR', 1000)->nullable()->default('');
            $table->string('currencycode', 5)->nullable();
            $table->decimal('currencyrate', 10, 6)->nullable();
            $table->string('currencysymbol', 10)->nullable();
            $table->decimal('free_shipping_over', 18, 3)->default(0);
            $table->string('isocode', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countrymaster');
    }
};