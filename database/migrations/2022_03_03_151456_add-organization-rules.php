<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrganizationRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_guidelines', function (Blueprint $table) {
            $table->boolean('singular_they')->nullable();
            $table->boolean('expert_mode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_guidelines', function (Blueprint $table) {
            $table->dropColumn([
                'singular_they',
                'expert_mode',
            ]);
        });
    }
}
