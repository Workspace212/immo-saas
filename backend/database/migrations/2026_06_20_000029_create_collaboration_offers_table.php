<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaboration_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('offer_number')->index();
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('MAD');
            $table->string('status')->default('submitted')->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('accepted_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collaboration_id', 'offer_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_offers');
    }
};
