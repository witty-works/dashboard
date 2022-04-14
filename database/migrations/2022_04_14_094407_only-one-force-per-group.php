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
        DB::table('organization_guidelines')
            ->update([
                "preferred_variants_force" => null,
            ]);
        Schema::table('organization_guidelines', function ($table) {
            $table->renameColumn('singular_they_force', 'english_rules_force');
            $table->renameColumn('german_gender_ending_force', 'german_rules_force');
            $table->dropColumn('gendered_roles_format_force');
            $table->boolean('preferred_variants_force')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('organization_guidelines')
            ->update([
                "preferred_variants_force" => null,
            ]);
        Schema::table('organization_guidelines', function ($table) {
            $table->renameColumn('english_rules_force', 'singular_they_force');
            $table->renameColumn('german_rules_force', 'german_gender_ending_force');
            $table->boolean('gendered_roles_format_force')->nullable();
            $table->json('preferred_variants_force')->nullable()->change();
        });
    }
};
