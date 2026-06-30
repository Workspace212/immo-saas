<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaboration_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->boolean('is_internal')->default(true)->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_messages');
    }
};
