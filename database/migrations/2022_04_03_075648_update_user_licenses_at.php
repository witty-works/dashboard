<?php

use Illuminate\Database\Migrations\Migration;
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
        Schema::table('teams', function ($table) {
            $table->dropColumn('user_licenses_updated_at');
        });
        Schema::table('subscriptions', function ($table) {
            $table->datetime('update_user_licenses_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function ($table) {
            $table->date('user_licenses_updated_at')->nullable();
        });
        Schema::table('subscriptions', function ($table) {
            $table->dropColumn('update_user_licenses_at');
        });
    }
};
