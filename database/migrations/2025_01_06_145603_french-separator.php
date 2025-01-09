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
        Schema::table('language_guidelines', function ($table) {
            $table->boolean('french_rules_force')->nullable();
            $table->string('french_gender_separator')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('language_guidelines', function ($table) {
            $table->dropColumn('french_rules_force');
            $table->dropColumn('french_gender_separator');
        });
    }
};
