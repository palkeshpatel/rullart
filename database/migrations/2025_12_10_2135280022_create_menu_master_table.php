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
        Schema::create('menu_master', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('parent_id')->nullable();
            $table->string('menu_name', 100)->nullable();
            $table->string('page_url', 255)->nullable();
            $table->string('icon_name', 50)->nullable();
            $table->integer('display_order')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->integer('is_delete')->nullable()->default(0);
            $table->integer('is_direct')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_master');
    }
};