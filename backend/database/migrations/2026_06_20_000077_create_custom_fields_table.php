<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('module')->index();
            $table->string('field_key')->index();
            $table->string('label');
            $table->string('field_type')->index();
            $table->boolean('is_required')->default(false)->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'module', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
