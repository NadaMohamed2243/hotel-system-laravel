<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceptionistsTable extends Migration
{
    public function up()
    {
        Schema::create('receptionists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign key to users table
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade'); // Foreign key to users table (manager)
            $table->boolean('is_banned')->default(false); // Receptionist-specific field
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('receptionists');
    }
}
