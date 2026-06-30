<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('api_client_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('event')->index();
            $table->string('http_method')->default('POST')->index();
            $table->string('secret')->nullable();
            $table->json('headers')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'event', 'url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_webhooks');
    }
};
