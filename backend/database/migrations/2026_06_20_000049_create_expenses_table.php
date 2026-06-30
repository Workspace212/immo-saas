<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_category_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('financial_account_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('complaint_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('expense_number');
            $table->date('expense_date')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount_ht', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('amount_ttc', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('payment_method')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->timestamp('validated_at')->nullable()->index();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'expense_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
