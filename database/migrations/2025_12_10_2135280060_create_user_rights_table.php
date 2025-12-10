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
        Schema::create('user_rights', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('role_id')->nullable();
            $table->integer('menu_id')->nullable();
            $table->integer('view_button')->nullable()->default(1);
            $table->integer('add_button')->nullable()->default(0);
            $table->integer('edit_button')->nullable()->default(0);
            $table->integer('delete_button')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rights');
    }
};