<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('report_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->foreignId('saved_report_id')->nullable()->index()->constrained()->cascadeOnDelete();
            $table->foreignId('shared_with_user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('role')->nullable()->index();
            $table->boolean('can_view')->default(true)->index();
            $table->boolean('can_edit')->default(false)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['report_id', 'saved_report_id', 'shared_with_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_shares');
    }
};
