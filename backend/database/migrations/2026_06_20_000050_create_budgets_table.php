<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('financial_category_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('revenue_center_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('budget_number');
            $table->string('name');
            $table->integer('budget_year')->index();
            $table->string('period_type')->index();
            $table->integer('period_number')->nullable()->index();
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('consumed_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('currency')->default('MAD');
            $table->decimal('alert_threshold_percent', 5, 2)->default(80);
            $table->boolean('alerts_enabled')->default(true)->index();
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'budget_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
