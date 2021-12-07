<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorporateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->index()->unique();
            $table->json('preferred_languages')->nullable();
            $table->json('preferred_variants')->nullable();
            $table->string('german_gender_ending')->nullable();
            $table->json('disabled_categories')->nullable();
            $table->string('gendered_roles_format')->nullable();
            $table->boolean('store_context')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corporate_rules');
    }
}
