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
        Schema::create('contract_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_payment_schedule_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('received_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('payment_number')->unique()->index();
            $table->timestamp('payment_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('payment_method')->index();
            $table->string('reference')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('status')->default('completed')->index();
            $table->string('receipt_number')->nullable()->index();
            $table->timestamp('receipt_generated_at')->nullable();
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payments');
    }
};
