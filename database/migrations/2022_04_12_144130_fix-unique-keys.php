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
            $table->dropUnique(['false_positive']);
            $table->unique(['team_id', 'false_positive']);
        });
        Schema::table('term_replacements', function ($table) {
            $table->dropUnique(['term']);
            $table->unique(['team_id', 'term']);
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
            $table->dropUnique(['team_id', 'false_positive']);
            $table->unique(['false_positive']);
        });
        Schema::table('term_replacements', function ($table) {
            $table->dropUnique(['team_id', 'term']);
            $table->unique(['term']);
        });
    }
};
