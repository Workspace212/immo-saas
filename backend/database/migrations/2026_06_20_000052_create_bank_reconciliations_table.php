<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_account_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('reconciliation_number');
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->decimal('bank_balance', 15, 2)->default(0);
            $table->decimal('system_balance', 15, 2)->default(0);
            $table->decimal('difference_amount', 15, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamp('validated_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'reconciliation_number']);
            $table->unique(['financial_account_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
    }
};
