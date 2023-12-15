<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lietotāja datu bāzes tabulas shēma
     */
    public function up()
    {
        Schema::rename('users', 'user'); 
        Schema::table('user', function (Blueprint $table) {
            $table->date('birthdate')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('birthdate'); 
        });
        Schema::rename('user', 'users'); 
    }
};
