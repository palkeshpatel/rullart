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
        Schema::create('newsletter', function (Blueprint $table) {
            $table->integer('newsletterid')->primary();
            $table->string('subject', 500)->nullable();
            $table->text('body')->nullable();
            $table->date('createdon')->nullable();
            $table->integer('status')->nullable();
            $table->date('completedon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter');
    }
};