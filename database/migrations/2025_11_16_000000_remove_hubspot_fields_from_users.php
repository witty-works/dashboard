<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hubspot_id',
                'hubspot_source',
                'hubspotutk',
                'hubspot_company_id',
                'hubspot_last_sync',
                'hubspot_sales_readiness',
            ]);
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('hubspot_id')->nullable();
            $table->string('hubspot_source')->nullable();
            $table->string('hubspotutk')->nullable();
            $table->bigInteger('hubspot_company_id')->unsigned()->nullable();
            $table->date('hubspot_last_sync')->nullable();
            $table->string('hubspot_sales_readiness')->nullable();
        });
    }
};
