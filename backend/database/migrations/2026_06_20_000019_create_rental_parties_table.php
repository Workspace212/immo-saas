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
        Schema::create('rental_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_unit_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();
            $table->string('party_type')->index();
            $table->string('role')->index();
            $table->integer('display_order')->default(0)->index();
            $table->boolean('signed')->default(false)->index();
            $table->timestamp('signature_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['rental_unit_id', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_parties');
    }
};
