<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Recipient
            $table->string('type'); // e.g., 'service_booked', 'review_left', 'upcoming_reminder'

            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->string('service_title')->nullable(); // cache

            $table->foreignId('reguser_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reguser_name')->nullable(); // cache

            $table->date('date')->nullable();
            $table->time('time')->nullable();

            $table->boolean('read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
