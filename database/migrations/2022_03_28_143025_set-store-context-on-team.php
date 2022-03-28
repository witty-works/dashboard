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
        Schema::table('organization_guidelines', function ($table) {
            $table->dropColumn('store_context');
        });
        Schema::table('teams', function ($table) {
            $table->boolean('store_context')->nullable()->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_guidelines', function ($table) {
            $table->boolean('store_context')->nullable();
        });
        Schema::table('teams', function ($table) {
            $table->dropColumn('store_context');
        });
    }
};
