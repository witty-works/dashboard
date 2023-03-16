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
        Schema::table('users', function ($table) {
            $table->datetime('hubspot_last_sync')->change();
            $table->datetime('posthog_last_sync')->change();
        });

        Schema::table('teams', function ($table) {
            $table->datetime('posthog_last_sync')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->date('hubspot_last_sync')->change();
            $table->date('posthog_last_sync')->change();
        });

        Schema::table('teams', function ($table) {
            $table->date('posthog_last_sync')->change();
        });
    }
};
