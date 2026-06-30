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
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained();
            $table->string('type')->default('individual')->index();
            $table->string('full_name')->nullable();
            $table->string('cin_passport')->nullable();
            $table->string('phone')->nullable()->index();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Morocco');
            $table->string('company_name')->nullable();
            $table->string('ice')->nullable();
            $table->string('rc')->nullable();
            $table->string('if_number')->nullable();
            $table->string('patente')->nullable();
            $table->string('representative_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('rib_iban')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owners');
    }
};
