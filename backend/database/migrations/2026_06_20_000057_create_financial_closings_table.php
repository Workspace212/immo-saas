<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('closed_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('closing_number');
            $table->string('closing_type')->index();
            $table->integer('period_year')->index();
            $table->integer('period_month')->nullable()->index();
            $table->timestamp('closed_at')->nullable()->index();
            $table->boolean('is_locked')->default(false)->index();
            $table->timestamp('locked_at')->nullable();
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('total_expense', 15, 2)->default(0);
            $table->decimal('net_result', 15, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'closing_type', 'period_year', 'period_month']);
            $table->unique(['agency_id', 'closing_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_closings');
    }
};
