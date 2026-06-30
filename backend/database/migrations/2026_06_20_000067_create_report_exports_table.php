<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('report_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('saved_report_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->string('export_number');
            $table->string('export_format')->index();
            $table->string('status')->default('pending')->index();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->json('parameters')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['agency_id', 'export_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
