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
        Schema::create('mandates', function (Blueprint $table) {
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
            $table->foreignId('owner_id')
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->index()
                ->constrained()
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
            $table->string('mandate_number');
            $table->string('party_type')->index();
            $table->string('mandate_type')->default('simple')->index();
            $table->string('operation_type')->nullable()->index();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->string('commission_type')->nullable();
            $table->decimal('commission_value', 10, 2)->nullable();
            $table->text('commission_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'mandate_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandates');
    }
};
