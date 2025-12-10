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
        Schema::create('couponcode', function (Blueprint $table) {
            $table->integer('couponcodeid')->primary();
            $table->string('couponcode', 50)->nullable();
            $table->decimal('couponvalue', 10, 0)->nullable();
            $table->integer('isactive')->nullable()->default(1);
            $table->integer('isgeneral')->default(0);
            $table->integer('fkcoupontypeid')->default(1);
            $table->date('startdate')->nullable();
            $table->date('enddate')->nullable();
            $table->integer('ismultiuse')->nullable();
            $table->string('coupontype', 20)->nullable();
            $table->string('fkcategoryid', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couponcode');
    }
};