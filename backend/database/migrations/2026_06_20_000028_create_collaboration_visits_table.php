<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaboration_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamp('visit_date')->index();
            $table->string('status')->default('scheduled')->index();
            $table->text('feedback')->nullable();
            $table->string('result')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_visits');
    }
};
