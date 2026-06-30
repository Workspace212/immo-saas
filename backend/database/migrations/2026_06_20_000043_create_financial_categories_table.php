<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->index()->constrained('financial_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category_type')->index();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'slug', 'category_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};
