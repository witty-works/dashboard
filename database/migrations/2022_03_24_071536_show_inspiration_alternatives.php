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
        Schema::table('organization_guidelines', function (Blueprint $table) {
            $table->boolean('show_inspiration_alternatives')->nullable();
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
                'show_inspiration_alternatives',
            ]);
        });
    }
};
