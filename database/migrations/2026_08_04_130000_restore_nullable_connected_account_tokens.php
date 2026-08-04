<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same defect as 2026_08_04_120000: a bare ->change() drops every modifier that
 * is not repeated.
 *
 * connected_accounts.refresh_token was created nullable, and 2021_11_16_081539
 * deliberately made token nullable too. Then 2022_12_13_144515_token_length and
 * 2023_03_30_131123_refresh_token_size widened both columns with a plain
 * ->change() and silently made them NOT NULL again.
 *
 * App\Actions\Socialstream\CreateConnectedAccount inserts refresh_token => null
 * whenever the provider does not return one — which Microsoft Office SSO does
 * not — so linking an account failed outright under STRICT_TRANS_TABLES.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->string('token', 4000)->nullable()->change();
            $table->string('refresh_token', 4000)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->string('token', 4000)->nullable(false)->change();
            $table->string('refresh_token', 4000)->nullable(false)->change();
        });
    }
};
