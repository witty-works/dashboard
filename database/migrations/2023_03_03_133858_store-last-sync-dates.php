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
            $table->date('hubspot_last_sync')->nullable();
            $table->date('posthog_last_sync')->nullable();
            $table->json('posthog_last_sync_data')->nullable();
        });

        Schema::table('teams', function ($table) {
            $table->date('posthog_last_sync')->nullable();
            $table->json('posthog_last_sync_data')->nullable();
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
            $table->dropColumn('hubspot_last_sync');
            $table->dropColumn('posthog_last_sync');
            $table->dropColumn('posthog_last_sync_data');
        });

        Schema::table('teams', function ($table) {
            $table->dropColumn('posthog_last_sync');
            $table->dropColumn('posthog_last_sync_data');
        });
    }
};
