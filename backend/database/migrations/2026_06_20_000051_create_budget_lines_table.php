<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('budget_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_category_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('period_type')->index();
            $table->integer('period_number')->nullable()->index();
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance_amount', 15, 2)->default(0);
            $table->string('currency')->default('MAD');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['budget_id', 'name', 'period_type', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
