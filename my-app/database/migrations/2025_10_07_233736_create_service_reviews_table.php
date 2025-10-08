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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('reguser_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('service_id')
                ->constrained()
                ->onDelete('cascade');

            // Review content
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();

            $table->timestamps();

            //allows multiple reviews per person
            $table->index(['service_id']);
            $table->index(['reguser_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews'); // fixed wrong table name
    }
};
