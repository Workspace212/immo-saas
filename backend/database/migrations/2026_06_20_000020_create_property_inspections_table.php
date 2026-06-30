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
        Schema::create('property_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('rental_unit_id')
                ->nullable()
                ->index()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('property_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('created_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('validated_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('inspection_number');
            $table->string('inspection_type')->index();
            $table->timestamp('inspection_date')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->decimal('electricity_meter', 12, 2)->nullable();
            $table->decimal('water_meter', 12, 2)->nullable();
            $table->decimal('gas_meter', 12, 2)->nullable();
            $table->integer('keys_given')->default(0);
            $table->integer('remotes_given')->default(0);
            $table->integer('access_cards_given')->default(0);
            $table->string('global_condition')->nullable();
            $table->text('tenant_comments')->nullable();
            $table->text('agency_comments')->nullable();
            $table->timestamp('validation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'inspection_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inspections');
    }
};
