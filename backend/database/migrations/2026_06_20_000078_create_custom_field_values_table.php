<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('custom_field_id')->index()->constrained()->cascadeOnDelete();
            $table->string('record_type')->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->json('value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['custom_field_id', 'record_type', 'record_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
