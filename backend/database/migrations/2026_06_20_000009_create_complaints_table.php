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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('property_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('assigned_to')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('complaint_number');
            $table->string('complaint_type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('new')->index();
            $table->string('provider_name')->nullable();
            $table->string('provider_phone')->nullable();
            $table->date('intervention_date')->nullable()->index();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'complaint_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
