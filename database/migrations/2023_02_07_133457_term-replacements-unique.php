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
        Schema::table('term_replacements', function ($table) {
            $table->dropUnique(['team_id', 'term']);
            $table->unique(['team_id', 'term', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('term_replacements', function ($table) {
            $table->dropUnique(['team_id', 'term', 'language_code']);
            $table->unique(['team_id', 'term']);
        });
    }
};
