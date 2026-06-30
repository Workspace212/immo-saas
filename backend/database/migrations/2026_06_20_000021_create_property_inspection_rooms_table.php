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
        Schema::create('property_inspection_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_inspection_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('room_name')->index();
            $table->string('room_type')->nullable()->index();
            $table->string('floor')->nullable();
            $table->string('condition')->nullable()->index();
            $table->string('cleanliness')->nullable()->index();
            $table->text('comments')->nullable();
            $table->integer('display_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_inspection_id', 'room_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inspection_rooms');
    }
};
