<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_account_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('payment_number');
            $table->timestamp('payment_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('payment_method')->index();
            $table->string('reference')->nullable()->index();
            $table->string('receipt_number')->nullable()->index();
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('completed')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'payment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
