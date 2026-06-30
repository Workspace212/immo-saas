<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('integration_type')->index();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->json('credentials')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'provider', 'integration_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrations');
    }
};
