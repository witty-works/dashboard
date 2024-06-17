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
            $table->string('name')->nullable();
        });

        Schema::table('kpis', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'user_id', 'date', 'kpi']);
        });

        Schema::table('kpis', function (Blueprint $table) {
            $table->unique(['team_id', 'user_id', 'date', 'kpi', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('kpis', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'user_id', 'date', 'kpi', 'name']);
        });

        Schema::table('kpis', function (Blueprint $table) {
            $table->unique(['team_id', 'user_id', 'date', 'kpi']);
        });
    }
};
