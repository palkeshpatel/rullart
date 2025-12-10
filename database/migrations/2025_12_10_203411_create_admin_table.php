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
        Schema::create('admin', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('user', 20)->nullable();
            $table->string('pass', 32)->nullable();
            $table->text('name')->nullable();
            $table->text('email')->nullable();
            $table->unsignedInteger('site')->default(0);
            $table->integer('user_role')->nullable();
            $table->date('d_add')->nullable();
            $table->unsignedBigInteger('d_mod')->nullable();
            $table->integer('lock_access')->nullable();
            $table->integer('fkstoreid')->nullable();
            $table->dateTime('created_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
