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
        DB::statement('UPDATE teams SET trial_ends_at = CONCAT(DATE(trial_ends_at), \' 23:59:59\') WHERE trial_ends_at IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
