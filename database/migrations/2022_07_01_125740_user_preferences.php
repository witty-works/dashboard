<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('organization_guidelines', 'language_guidelines');

        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->foreignId('team_id')->index()->unique()->nullable()->change();
            $table->foreignId('user_id')->index()->unique()->nullable();
        });

        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->dropUnique('corporate_rules_team_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('language_guidelines')->whereNull('team_id')->delete();

        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->dropUnique('language_guidelines_team_id_unique');
            $table->dropUnique('language_guidelines_user_id_unique');
            $table->dropColumn('user_id');
        });

        Schema::rename('language_guidelines', 'corporate_rules');

        Schema::table('corporate_rules', function (Blueprint $table) {
            $table->foreignId('team_id')->index()->unique()->nullable(false)->change();
        });

        Schema::rename('corporate_rules', 'organization_guidelines');
    }
};
