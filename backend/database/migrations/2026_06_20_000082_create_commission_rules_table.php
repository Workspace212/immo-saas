<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('contract_type_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('team_name')->nullable()->index();
            $table->decimal('percentage', 10, 2)->nullable();
            $table->decimal('fixed_amount', 15, 2)->nullable();
            $table->string('currency')->default('MAD');
            $table->integer('priority')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->json('conditions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
