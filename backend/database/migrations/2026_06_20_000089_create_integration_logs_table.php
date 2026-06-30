<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('integration_id')->index()->constrained()->cascadeOnDelete();
            $table->string('action')->index();
            $table->string('result')->index();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('logged_at')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
