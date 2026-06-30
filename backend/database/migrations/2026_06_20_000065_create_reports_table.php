<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('report_number')->nullable();
            $table->string('name');
            $table->string('report_type')->index();
            $table->string('category')->index();
            $table->json('parameters')->nullable();
            $table->json('filters')->nullable();
            $table->date('period_start')->nullable()->index();
            $table->date('period_end')->nullable()->index();
            $table->string('visibility')->default('private')->index();
            $table->boolean('is_favorite')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'report_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
