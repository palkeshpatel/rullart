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
        Schema::create('orderlimit_emails', function (Blueprint $table) {
            $table->integer('orderlimitid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->string('email', 300)->nullable();
            $table->decimal('ordertotal', 18, 3)->nullable();
            $table->decimal('totallimit', 18, 3)->nullable();
            $table->date('sendon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orderlimit_emails');
    }
};