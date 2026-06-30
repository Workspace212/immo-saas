<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaboration_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->index()->constrained('users')->cascadeOnDelete();
            $table->string('commission_role')->index();
            $table->string('commission_type')->index();
            $table->decimal('commission_value', 10, 2);
            $table->decimal('calculated_amount', 15, 2)->nullable();
            $table->string('currency')->default('MAD');
            $table->string('status')->default('pending')->index();
            $table->foreignId('validated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_commissions');
    }
};
