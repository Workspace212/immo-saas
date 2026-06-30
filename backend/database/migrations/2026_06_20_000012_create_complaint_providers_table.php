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
        Schema::create('complaint_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('provider_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->date('intervention_date')->nullable()->index();
            $table->string('status')->default('assigned')->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['complaint_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_providers');
    }
};
