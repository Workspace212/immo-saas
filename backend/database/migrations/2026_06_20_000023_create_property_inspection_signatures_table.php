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
        Schema::create('property_inspection_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_inspection_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('signer_type')->index();
            $table->string('signer_name');
            $table->string('signer_role')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('signed')->default(false)->index();
            $table->timestamp('signed_at')->nullable()->index();
            $table->boolean('refused')->default(false)->index();
            $table->text('refusal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inspection_signatures');
    }
};
