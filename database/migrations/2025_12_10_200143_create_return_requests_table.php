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
        Schema::create('returnrequest', function (Blueprint $table) {
            $table->integer('requestid')->primary();
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('orderno', 20)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('reason', 5000)->nullable();
            $table->dateTime('submiton')->nullable();
            $table->string('lang', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returnrequest');
    }
};
