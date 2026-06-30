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
        Schema::create('client_property_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('property_type_id')->nullable()->index()->constrained();
            $table->string('city')->nullable()->index();
            $table->string('sector')->nullable();
            $table->decimal('min_budget', 15, 2)->nullable();
            $table->decimal('max_budget', 15, 2)->nullable();
            $table->decimal('min_living_area_m2', 10, 2)->nullable();
            $table->decimal('min_land_area_m2', 10, 2)->nullable();
            $table->integer('min_bedrooms')->nullable();
            $table->integer('min_bathrooms')->nullable();
            $table->string('activity_type')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_property_requests');
    }
};
