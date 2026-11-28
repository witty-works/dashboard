<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Two-factor sign-in takes effect only once a code from the app has been
    // entered (config/fortify.php, 'confirm'). Secrets stored before this
    // column existed stay unconfirmed: the old profile form never showed the
    // QR code, so nobody could have scanned them.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_confirmed_at');
        });
    }
};
