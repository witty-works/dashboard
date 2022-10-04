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
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->boolean('simple_language')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->dropColumn([
                'simple_language',
            ]);
        });
    }
};
