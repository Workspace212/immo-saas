<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('contract_payment_schedule_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('financial_account_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('disbursement_number');
            $table->decimal('amount_due', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('currency')->default('MAD');
            $table->date('scheduled_date')->nullable()->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->string('payment_method')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('receipt_number')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'disbursement_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_disbursements');
    }
};
