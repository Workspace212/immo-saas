<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('code')->index();
            $table->string('center_type')->index();
            $table->string('project_name')->nullable()->index();
            $table->decimal('revenue_total', 15, 2)->default(0);
            $table->decimal('expense_total', 15, 2)->default(0);
            $table->decimal('profit_total', 15, 2)->default(0);
            $table->json('statistics')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_centers');
    }
};
