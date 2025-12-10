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
        Schema::create('notifications', function (Blueprint $table) {
            $table->integer('notificationid')->primary();
            $table->integer('fkcustomerid')->nullable();
            $table->string('device_uid', 500)->nullable();
            $table->string('title', 500)->nullable();
            $table->string('message', 5000)->nullable();
            $table->integer('isread')->default(0);
            $table->integer('createdby')->nullable();
            $table->date('createdon')->nullable();
            $table->string('response', 1000)->nullable();
            $table->integer('badge')->nullable();
            $table->string('redirect_type', 100)->nullable();
            $table->string('redirect_code', 500)->nullable();
            $table->string('photo', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};