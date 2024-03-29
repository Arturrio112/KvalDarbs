<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pievieno 2 kolonas profila tabulai
     */
    public function up(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->string('fontColor')->nullable();
            $table->string('borderColor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropColumn('fontColor');
            $table->dropColumn('borderColor');
        });
    }
};
