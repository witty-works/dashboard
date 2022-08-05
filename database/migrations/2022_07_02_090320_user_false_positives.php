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
        Schema::table('false_positives', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->change();
            $table->foreignId('user_id')->index()->nullable();
            $table->unique(['user_id', 'false_positive']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('false_positives')->whereNull('team_id')->delete();

        Schema::table('false_positives', function (Blueprint $table) {
            $table->dropIndex('false_positives_user_id_index');
            $table->dropUnique(['user_id', 'false_positive']);
            $table->foreignId('team_id')->nullable(false)->change();
            $table->dropColumn('user_id');
        });
    }
};
