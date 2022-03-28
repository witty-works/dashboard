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
            $table->boolean('singular_they_force')->nullable();
            $table->boolean('expert_mode_force')->nullable();
            $table->json('preferred_variants_force')->nullable();
            $table->json('disabled_categories_force')->nullable();
            $table->boolean('german_gender_ending_force')->nullable();
            $table->boolean('gendered_roles_format_force')->nullable();
            $table->boolean('show_inspiration_alternatives_force')->nullable();
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
            $table->dropColumn('singular_they_force');
            $table->dropColumn('expert_mode_force');
            $table->dropColumn('preferred_variants_force');
            $table->dropColumn('disabled_categories_force');
            $table->dropColumn('german_gender_ending_force');
            $table->dropColumn('gendered_roles_format_force');
            $table->dropColumn('show_inspiration_alternatives_force');
        });
    }
};
