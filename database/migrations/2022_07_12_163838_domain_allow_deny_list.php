<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('team_id')->nullable();
            $table->string('domain');
            $table->timestamps();

            $table->unique(['domain', 'user_id']);
            $table->unique(['domain', 'team_id']);
        });

        Schema::table('users', function ($table) {
            $table->string('config_hash')->nullable();
        });

        Schema::table('teams', function ($table) {
            $table->string('config_hash')->nullable();
        });

        Schema::table('language_guidelines', function ($table) {
            $table->enum('domain_list_type', ['allow', 'allow_witty_works', 'deny'])->default('deny');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains');

        Schema::table('users', function ($table) {
            $table->dropColumn('config_hash');
        });

        Schema::table('teams', function ($table) {
            $table->dropColumn('config_hash');
        });

        Schema::table('language_guidelines', function ($table) {
            $table->dropColumn('domain_list_type');
        });
    }
};
