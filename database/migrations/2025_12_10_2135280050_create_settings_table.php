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
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('settingid')->primary();
            $table->string('name', 100)->nullable();
            $table->string('details', 1000)->nullable();
            $table->string('inputtype', 15)->nullable();
            $table->boolean('isrequired')->nullable();
            $table->integer('displayorder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};