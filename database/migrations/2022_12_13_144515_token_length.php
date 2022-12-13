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
        Schema::table('connected_accounts', function ($table) {
            $table->string('token', 2000)->change();
            $table->string('refresh_token', 2000)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connected_accounts', function ($table) {
            $table->string('token', 1000)->change();
            $table->string('refresh_token', 1000)->change();
        });
    }
};
