<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('requesting_agent_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_agent_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('collaboration_number');
            $table->string('collaboration_type')->default('property_client_match')->index();
            $table->string('status')->default('pending')->index();
            $table->text('request_message')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('accepted_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'collaboration_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborations');
    }
};
