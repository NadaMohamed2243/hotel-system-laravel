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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Reference to clients table
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade'); // Reference to rooms table
            $table->integer('accompany_number'); // Number of accompanying guests (must not exceed room capacity)
            $table->unsignedBigInteger('paid_price'); // Price stored in cents
            $table->timestamp('reserved_at')->useCurrent(); // Auto-filled reservation date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
