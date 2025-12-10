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
        // Note: Customer table structure from CI - adjust based on actual table name
        Schema::create('customer', function (Blueprint $table) {
            $table->integer('customerid')->primary();
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('email', 100)->unique();
            $table->string('mobile', 20)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('login_type', 50)->nullable();
            $table->integer('site')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('register_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
