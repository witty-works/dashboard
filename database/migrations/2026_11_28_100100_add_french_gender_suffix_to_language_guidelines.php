<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The French feminine suffix next to the separator: `short` writes
 * utilisateur·rice, `full` utilisateur·trice. Locked with the separator by
 * `french_rules_force`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->string('french_gender_suffix')->default('short')->after('french_gender_separator');
        });
    }

    public function down(): void
    {
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->dropColumn('french_gender_suffix');
        });
    }
};
