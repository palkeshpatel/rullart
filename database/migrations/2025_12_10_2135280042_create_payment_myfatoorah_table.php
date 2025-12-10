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
        Schema::create('payment_myfatoorah', function (Blueprint $table) {
            $table->integer('payid')->primary();
            $table->string('InvoiceId', 20)->nullable();
            $table->string('InvoiceStatus', 20)->nullable();
            $table->string('InvoiceReference', 20)->nullable();
            $table->string('CustomerReference', 20)->nullable();
            $table->date('CreatedDate')->nullable();
            $table->string('ExpiryDate', 20)->nullable();
            $table->decimal('InvoiceValue', 18, 3)->nullable();
            $table->string('Comments', 1500)->nullable();
            $table->string('CustomerName', 100)->nullable();
            $table->string('CustomerMobile', 50)->nullable();
            $table->string('CustomerEmail', 100)->nullable();
            $table->string('UserDefinedField', 30)->nullable();
            $table->string('InvoiceDisplayValue', 50)->nullable();
            $table->date('TransactionDate')->nullable();
            $table->string('PaymentGateway', 50)->nullable();
            $table->string('ReferenceId', 30)->nullable();
            $table->string('TrackId', 30)->nullable();
            $table->string('TransactionId', 30)->nullable();
            $table->string('PaymentId', 30)->nullable();
            $table->string('PaymentId_query', 30)->nullable();
            $table->string('AuthorizationId', 30)->nullable();
            $table->string('TransactionStatus', 20)->nullable();
            $table->decimal('TransationValue', 18, 3)->nullable();
            $table->decimal('CustomerServiceCharge', 18, 3)->nullable();
            $table->decimal('DueValue', 18, 3)->nullable();
            $table->string('PaidCurrency', 10)->nullable();
            $table->decimal('PaidCurrencyValue', 18, 3)->nullable();
            $table->string('Currency', 10)->nullable();
            $table->string('Error', 500)->nullable();
            $table->string('CardNumber', 50)->nullable();
            $table->string('response', 5000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_myfatoorah');
    }
};