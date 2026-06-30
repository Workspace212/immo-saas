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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained();
            $table->foreignId('property_type_id')->nullable()->index()->constrained();
            $table->string('reference');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('country')->default('Morocco');
            $table->string('city')->nullable()->index();
            $table->string('sector')->nullable()->index();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('living_area_m2', 10, 2)->nullable();
            $table->decimal('land_area_m2', 10, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('floors')->nullable();
            $table->integer('year_built')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_published')->default(false)->index();
            $table->string('confidentiality_level')->default('internal')->index();
            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
