<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The team's share of the shared model capacity: LLM requests per minute,
    // set by superadmins only. Null means no per-team limit.
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('llm_requests_per_minute')->nullable()->after('personal_team');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('llm_requests_per_minute');
        });
    }
};
