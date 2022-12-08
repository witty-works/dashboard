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
        Schema::table('users', function ($table) {
            $table->string('role')->nullable();
        });

        DB::table('users')
            ->whereRaw('NOT EXISTS (SELECT * FROM teams WHERE teams.user_id = users.id AND users.current_team_id = teams.id)')
            ->update([
                'role' => '',
            ]);

        Schema::table('language_guidelines', function ($table) {
            $table->boolean('customized')->nullable()->default(true);
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
            $table->dropColumn('role');
        });

        Schema::table('language_guidelines', function ($table) {
            $table->dropColumn('customized');
        });
    }
};
