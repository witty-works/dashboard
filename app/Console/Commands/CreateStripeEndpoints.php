<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Stripe;

class CreateStripeEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spark:create-stripe-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes all current endpoints and creates endpoints in Stripe based on the Spark app';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Stripe\Stripe::setApiKey(config('cashier.secret'));

        $this->info('Creating endpoints ...');

        try {
            $this->deleteEndpoints();
            $this->createEndpoints();
        } catch (Stripe\Exception\InvalidRequestException $e) {
            $this->error($e->getMessage());
        }

        $this->info('Finished');
    }

    /**
     * Delete all endpoints in Stripe
     *
     * @param array $plans
     */
    protected function deleteEndpoints()
    {
        $endpoints = Stripe\WebhookEndpoint::all();
        foreach ($endpoints as $endpoint) {
            $this->warn('Deleted webhook endpoint:' . $endpoint->url);

            $endpoint->delete();
        }
    }

    /**
     * Try and create endpoints in Stripe
     *
     * @param array $plans
     */
    protected function createEndpoints()
    {
        $url = route('spark.webhook');

        Stripe\WebhookEndpoint::create([
            'url' => $url,
            'enabled_events' => [
                'customer.deleted',
                'customer.subscription.created',
                'customer.subscription.deleted',
                'customer.subscription.updated',
                'customer.updated',
                'invoice.payment_action_required',
                'invoice.payment_succeeded',
                #
            ]
        ]);

        $this->info('Created webhook endpoint:' . $url);
    }
}
