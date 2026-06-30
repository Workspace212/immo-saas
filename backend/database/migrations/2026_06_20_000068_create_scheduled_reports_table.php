<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('report_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('saved_report_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('frequency')->index();
            $table->time('send_time')->nullable();
            $table->json('recipients')->nullable();
            $table->boolean('email_enabled')->default(true)->index();
            $table->boolean('whatsapp_enabled')->default(false)->index();
            $table->timestamp('last_sent_at')->nullable()->index();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
