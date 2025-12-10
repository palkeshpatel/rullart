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
        Schema::create('productsphoto', function (Blueprint $table) {
            $table->integer('productimageid')->primary();
            $table->integer('fkproductid')->nullable();
            $table->string('photoname', 200)->nullable();
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productsphoto');
    }
};