<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('session_token')->unique();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('force_logged_out')->default(false)->index();
            $table->timestamp('force_logged_out_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
