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
        Schema::create('messages', function (Blueprint $table) {
            $table->integer('messageid')->primary();
            $table->string('message', 500)->nullable();
            $table->string('messageAR', 500)->nullable();
            $table->integer('isactive')->default(0);
            $table->integer('displayorder')->nullable()->default(99);
            $table->integer('displayorderAR')->nullable()->default(99);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};