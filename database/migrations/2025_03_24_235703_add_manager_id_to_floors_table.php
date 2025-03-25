<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema::table('floors', function (Blueprint $table) {
        //     $table->foreignId('manager_id')
        //           ->after('number')
        //           ->constrained('users')
        //           ->onDelete('cascade');
        // });
    }

    public function down()
    {
        // Schema::table('floors', function (Blueprint $table) {
        //     $table->dropForeign(['manager_id']);
        //     $table->dropColumn('manager_id');
        // });
    }
};
