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
        DB::table('term_replacements')
            ->where('word_type', '-')
            ->update([
                'word_type' => '~',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('term_replacements')
            ->where('word_type', '~')
            ->update([
                'word_type' => '-',
            ]);
    }
};
