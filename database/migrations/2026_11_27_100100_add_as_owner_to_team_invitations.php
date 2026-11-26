<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // An invitation to take a team over from the superadmin who set it up.
    public function up(): void
    {
        Schema::table('team_invitations', function (Blueprint $table) {
            $table->boolean('as_owner')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('team_invitations', function (Blueprint $table) {
            $table->dropColumn('as_owner');
        });
    }
};
