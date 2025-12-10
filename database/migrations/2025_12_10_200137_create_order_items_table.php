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
        Schema::create('orderitems', function (Blueprint $table) {
            $table->integer('orderitemid')->primary();
            $table->integer('fkorderid');
            $table->integer('fkproductid');
            $table->string('title', 100);
            $table->integer('qty');
            $table->decimal('price', 18, 3);
            $table->decimal('actualprice', 18, 3);
            $table->string('size', 25)->nullable();
            $table->string('sizename', 50)->nullable();
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 3);
            $table->string('photo', 100);
            $table->integer('fkstatusid')->nullable();
            $table->timestamps();
            $table->index('fkorderid');
            $table->index('fkproductid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderitems');
    }
};
