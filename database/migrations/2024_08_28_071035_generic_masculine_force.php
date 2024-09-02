<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->boolean('generic_masculine_force')->nullable();
        });

        DB::statement('UPDATE language_guidelines SET generic_masculine_force = german_rules_force');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('language_guidelines', function (Blueprint $table) {
            $table->dropColumn('generic_masculine_force');
        });
    }
};
