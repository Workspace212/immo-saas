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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('property_id')
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('previous_contract_id')
                ->nullable()
                ->index()
                ->constrained('contracts')
                ->nullOnDelete();
            $table->foreignId('assigned_agent_id')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('contract_number');
            $table->string('contract_type')->index();
            $table->string('status')->default('draft')->index();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->timestamp('signed_at')->nullable()->index();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency')->default('MAD');
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->decimal('charges_amount', 15, 2)->nullable();
            $table->string('agency_fee_type')->nullable();
            $table->decimal('agency_fee_value', 10, 2)->nullable();
            $table->decimal('agency_fee_amount', 15, 2)->nullable();
            $table->boolean('agency_fee_is_manual')->default(false);
            $table->decimal('owner_amount', 15, 2)->nullable();
            $table->boolean('owner_amount_is_manual')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'contract_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
