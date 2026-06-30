<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->timestamp('logged_in_at')->nullable()->index();
            $table->timestamp('logged_out_at')->nullable()->index();
            $table->boolean('successful')->default(false)->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
