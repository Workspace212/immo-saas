<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('commission_number');
            $table->string('operation_type')->index();
            $table->string('commission_type')->index();
            $table->decimal('commission_value', 10, 2);
            $table->decimal('base_amount', 15, 2)->nullable();
            $table->decimal('calculated_amount', 15, 2)->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('currency')->default('MAD');
            $table->boolean('is_manual')->default(false)->index();
            $table->string('status')->default('pending')->index();
            $table->timestamp('validated_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'commission_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
