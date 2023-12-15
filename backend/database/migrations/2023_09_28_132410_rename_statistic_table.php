<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pārsauc statistikas tabulu
     */
    public function up(): void
    {
        Schema::rename('statistic', 'statistics');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('statistics', 'statistic');
    }
};
