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
        Schema::create('emailtemplate', function (Blueprint $table) {
            $table->integer('emailtemplateid')->primary();
            $table->string('subject', 100)->nullable();
            $table->string('emailtype', 100)->nullable();
            $table->longText('body')->nullable();
            $table->date('createdon')->nullable();
            $table->date('completedon')->nullable();
            $table->string('status', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emailtemplate');
    }
};