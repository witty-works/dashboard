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
        Schema::create('term_replacements', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->string('replacement');
            $table->string('language_code', 2)->nullable();
            $table->foreignId('team_id')->index();
            $table->timestamps();
            $table->unique('term');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('term_replacements');
    }
};
