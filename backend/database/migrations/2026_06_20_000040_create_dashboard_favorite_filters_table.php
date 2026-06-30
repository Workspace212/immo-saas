<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_favorite_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('module')->index();
            $table->string('name');
            $table->string('search_query')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('is_favorite')->default(true)->index();
            $table->boolean('is_shared')->default(false)->index();
            $table->integer('display_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'user_id', 'module', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_favorite_filters');
    }
};
