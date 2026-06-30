<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('mandate_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('complaint_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('collaboration_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('appointment_number');
            $table->string('appointment_type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->string('location')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->string('priority')->default('normal')->index();
            $table->string('external_calendar_provider')->nullable()->index();
            $table->string('external_calendar_id')->nullable();
            $table->string('external_event_id')->nullable()->index();
            $table->string('sync_status')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'appointment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
