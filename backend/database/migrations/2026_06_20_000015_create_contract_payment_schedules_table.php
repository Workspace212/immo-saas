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
        Schema::create('contract_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('schedule_number')->index();
            $table->string('payment_type')->index();
            $table->date('due_date')->index();
            $table->decimal('amount_due', 15, 2);
            $table->string('currency')->default('MAD');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->integer('days_late')->default(0);
            $table->decimal('penalty_amount', 15, 2)->nullable();
            $table->boolean('penalty_is_manual')->default(false);
            $table->decimal('owner_amount_due', 15, 2)->nullable();
            $table->decimal('owner_amount_paid', 15, 2)->default(0);
            $table->string('owner_disbursement_status')->default('pending')->index();
            $table->string('receipt_number')->nullable()->index();
            $table->timestamp('receipt_generated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['contract_id', 'schedule_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_payment_schedules');
    }
};
