<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('owner_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('ownership_percentage', 5, 2)->default(100);
            $table->boolean('is_primary_owner')->default(false)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_owners');
    }
};
