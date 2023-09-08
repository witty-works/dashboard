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
        DB::table('subscriptions')
            ->update([
                'name' => 'witty',
            ]);

        DB::table('subscriptions')
            ->where('stripe_id', 'LIKE', 'invoice%')
            ->update([
                'stripe_price' => 'enterprise',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('subscriptions')
            ->update([
                'name' => 'teams',
                'stripe_price' => 'price_1Kl7JnCKySiDI8CQEbY6vN2H',
            ]);
    }
};
