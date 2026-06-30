<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('document_number');
            $table->string('document_type')->index();
            $table->string('title');
            $table->date('document_date')->nullable()->index();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency')->default('MAD');
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_documents');
    }
};
