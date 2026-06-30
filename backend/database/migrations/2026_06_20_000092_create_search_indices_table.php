<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_indices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('module')->index();
            $table->string('searchable_type')->index();
            $table->unsignedBigInteger('searchable_id')->index();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->json('index_data')->nullable();
            $table->timestamp('indexed_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'searchable_type', 'searchable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_indices');
    }
};
