<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * posthog_last_sync was added as a nullable column, but
 * 2023_03_16_074511_sync_timestamp changed its type with a bare ->change(),
 * which drops every modifier that is not repeated — so the column silently
 * became NOT NULL with no default.
 *
 * Nothing writes the column on insert (only SyncToPosthog sets it, later), so on
 * a database built from the migration history, creating a user or a team fails
 * with "Field 'posthog_last_sync' doesn't have a default value" under the
 * STRICT_TRANS_TABLES mode this application configures.
 *
 * This restores the original intent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->datetime('posthog_last_sync')->nullable()->change();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->datetime('posthog_last_sync')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->datetime('posthog_last_sync')->nullable(false)->change();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->datetime('posthog_last_sync')->nullable(false)->change();
        });
    }
};
