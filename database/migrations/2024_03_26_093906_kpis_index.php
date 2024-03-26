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
        Schema::table('kpis', function (Blueprint $table) {
            $table->index(['user_id', 'kpi', 'date']);
            $table->index(['team_id', 'kpi', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'kpi', 'date']);
            $table->dropIndex(['team_id', 'kpi', 'date']);
        });
    }
};
