<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops what is left of billing.
 *
 * The application stopped reading any of this when laravel/cashier and the
 * Stripe integration were removed; this migration removes the storage as well.
 *
 * DESTRUCTIVE. The subscription history and the Stripe customer/payment-method
 * references are deleted, and down() cannot bring the rows back — it only
 * restores the empty structure. Take a database dump before deploying.
 *
 * teams.user_licenses, teams.false_positives and teams.term_replacements go too:
 * they were per-team overrides of the plan's seat and list caps
 * (2022_03_29_062055_team-limits), and nothing is capped any more.
 */
return new class extends Migration
{
    public function up(): void
    {
        // subscription_items references subscriptions, so it goes first.
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('teams_stripe_id_index');
            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'trial_ends_at',
                'user_licenses',
                'false_positives',
                'term_replacements',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_accessed_stripe');
        });
    }

    /**
     * Restores the structure only. Any billing data is gone for good.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_accessed_stripe')->nullable();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('user_licenses')->nullable();
            $table->integer('false_positives')->nullable();
            $table->integer('term_replacements')->nullable();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->unique();
            $table->string('type');
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('renews_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->boolean('is_paid')->default(true);
            $table->string('witty_contract_id')->nullable();
            $table->string('company_name')->nullable();

            $table->index(['team_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'stripe_price']);
        });
    }
};
