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
        Schema::table('false_positives', function ($table) {
            $table->foreign('team_id')->references('id')->on('teams')
                ->constrained()
                ->onDelete('cascade');
        });
        Schema::table('term_replacements', function ($table) {
            $table->foreign('team_id')->references('id')->on('teams')
                ->constrained()
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('false_positives', function ($table) {
            $table->dropForeign('false_positives_team_id_foreign');
        });
        Schema::table('term_replacements', function ($table) {
            $table->dropForeign('term_replacements_team_id_foreign');
        });
    }
};
