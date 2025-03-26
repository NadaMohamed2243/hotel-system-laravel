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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // Must be at least 4 digits
            $table->integer('capacity');
            $table->integer('price');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // $table->foreignId('floor_id')->constrained()->onDelete('cascade');
            // $table->boolean('is_booked')->default(false);
            $table->boolean('is_reserved')->default(false); //H Add this line

            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
