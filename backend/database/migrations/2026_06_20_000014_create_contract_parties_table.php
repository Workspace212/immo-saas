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
        Schema::create('contract_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
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
            $table->string('party_type')->index();
            $table->string('role')->index();
            $table->decimal('ownership_percentage', 5, 2)->nullable();
            $table->integer('display_order')->default(0)->index();
            $table->boolean('signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['contract_id', 'party_type', 'owner_id']);
            $table->unique(['contract_id', 'party_type', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_parties');
    }
};
