<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('api_webhook_id')->index()->constrained()->cascadeOnDelete();
            $table->string('event')->index();
            $table->json('payload')->nullable();
            $table->longText('response_body')->nullable();
            $table->integer('http_code')->nullable()->index();
            $table->integer('duration_ms')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('attempted_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_webhook_logs');
    }
};
