<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('tax_id')->index()->constrained()->cascadeOnDelete();
            $table->decimal('rate', 5, 2);
            $table->date('starts_at')->index();
            $table->date('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tax_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
