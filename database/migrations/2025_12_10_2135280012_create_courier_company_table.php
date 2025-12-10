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
        Schema::create('courier_company', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name', 255)->nullable();
            $table->text('tracking_url')->nullable();
            $table->integer('isactive')->nullable()->default(1);
            $table->date('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_company');
    }
};