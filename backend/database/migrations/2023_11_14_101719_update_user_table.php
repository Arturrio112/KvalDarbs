<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Noņem kolonu no lietotāja tabulas
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Remove the stats_id column
            $table->dropColumn('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            // Remove the stats_id column
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
