<?php

namespace App\Providers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spark\Plan;
use Spark\Spark;

class SparkServiceProvider extends ServiceProvider
{
    public function register()
    {
        Spark::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Resolve the current team...
        Spark::billable(Team::class)->resolve(function (Request $request) {
            return $request->user()->currentTeam;
        });

        // Verify that the current user owns the team...
        Spark::billable(Team::class)->authorize(function (Team $billable, Request $request) {
            return $request->user() &&
                $request->user()->id == $billable->user_id;
        });

        Spark::billable(Team::class)->checkPlanEligibility(function (Team $billable, Plan $plan) {
            // if ($billable->projects > 5 && $plan->name == 'Basic') {
            //     throw ValidationException::withMessages([
            //         'plan' => 'You have too many projects for the selected plan.'
            //     ]);
            // }
        });

        Spark::billable(Team::class)->chargePerSeat('user', function ($billable) {
            return $billable->user_licenses;
        });

        $sparkPlans = config('spark.plans');

        if (is_array($sparkPlans)) {
            foreach ($sparkPlans as $planConfig) {
                $planConfig['features'] = $planConfig['features'] ?? [];
                foreach ($planConfig['features'] as $key => $feature) {
                    $feature['parameters'] = $feature['parameters'] ?? [];
                    $planConfig['features'][$key] = __('spark.feature_' . $feature['name'], $feature['parameters']);
                }

                Spark::plan('team', __('spark.name_' . $planConfig['name']), $planConfig['price_id'])
                    ->interval('yearly')
                    ->shortDescription(__('spark.description_' . $planConfig['name']))
                    ->features($planConfig['features']);
            }
        }
    }
}
