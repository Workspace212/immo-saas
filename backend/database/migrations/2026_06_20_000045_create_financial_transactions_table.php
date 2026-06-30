<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('source_account_id')->nullable()->index()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('destination_account_id')->nullable()->index()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('financial_category_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('revenue_center_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('transaction_number');
            $table->string('transaction_type')->index();
            $table->date('transaction_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('reference')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamp('validated_at')->nullable()->index();
            $table->boolean('is_reconciled')->default(false)->index();
            $table->timestamp('reconciled_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'transaction_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
