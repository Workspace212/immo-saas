<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('cash_account_id')->nullable()->index()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->index()->constrained('financial_accounts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('movement_number');
            $table->string('movement_type')->index();
            $table->timestamp('movement_date')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('reference')->nullable()->index();
            $table->text('comment')->nullable();
            $table->string('status')->default('completed')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'movement_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
