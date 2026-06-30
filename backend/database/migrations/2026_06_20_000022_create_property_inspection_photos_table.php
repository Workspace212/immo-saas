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
        Schema::create('property_inspection_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_inspection_room_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('uploaded_by')
                ->nullable()
                ->index()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('taken_at')->nullable()->index();
            $table->integer('display_order')->default(0)->index();
            $table->boolean('is_cover')->default(false)->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_inspection_photos');
    }
};
