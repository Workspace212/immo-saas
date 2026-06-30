<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->string('timezone')->default('Africa/Casablanca')->index();
            $table->string('language')->default('fr')->index();
            $table->string('default_currency')->default('MAD')->index();
            $table->string('date_format')->default('d/m/Y');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('agency_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_settings');
    }
};
