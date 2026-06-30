<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('module')->index();
            $table->string('keyword')->index();
            $table->unsignedInteger('frequency')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'module', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_suggestions');
    }
};
