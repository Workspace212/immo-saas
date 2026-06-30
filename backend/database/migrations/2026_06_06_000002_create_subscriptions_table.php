<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->index()->constrained();
            $table->foreignId('subscription_plan_id')->index()->constrained();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active')->index();
            $table->decimal('amount', 10, 2);
            $table->string('payment_status')->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
