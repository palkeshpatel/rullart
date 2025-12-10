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
        Schema::create('shoppingcartitems', function (Blueprint $table) {
            $table->integer('cartitemid')->primary();
            $table->integer('fkcartid')->nullable();
            $table->integer('fkproductid')->nullable();
            $table->string('title', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('price', 18, 3)->nullable();
            $table->decimal('sellingprice', 18, 3)->nullable();
            $table->string('size', 25)->nullable();
            $table->string('sizename', 50)->nullable();
            $table->decimal('discount', 18, 2)->nullable();
            $table->decimal('subtotal', 18, 3)->nullable();
            $table->string('photo', 100)->nullable();
            $table->integer('fkstatusid')->nullable();
            $table->integer('giftproductid')->default(0);
            $table->integer('giftproductid2')->default(0);
            $table->integer('giftproductid3')->nullable()->default(0);
            $table->integer('giftproductid4')->nullable();
            $table->date('createdon')->nullable();
            $table->integer('internation_ship')->nullable()->default(0);
            $table->decimal('giftproductprice', 18, 3)->nullable()->default(0);
            $table->decimal('giftproduct2price', 18, 3)->nullable()->default(0);
            $table->decimal('giftproduct3price', 18, 3)->nullable()->default(0);
            $table->string('giftproduct4price', 255)->nullable();
            $table->decimal('giftboxprice', 18, 3)->nullable()->default(0);
            $table->integer('giftmessageid')->nullable()->default(0);
            $table->integer('giftqty')->default(0);
            $table->string('giftmessage', 500)->nullable();
            $table->string('gifttitle', 500)->nullable();
            $table->string('gifttitleAR', 500)->nullable();
            $table->decimal('giftmessage_charge', 18, 3)->nullable()->default(0);
            $table->integer('gift_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoppingcartitems');
    }
};