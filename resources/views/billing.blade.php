<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('stripe.pricing') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="mt-10 sm:mt-0">
                @if ($team && $team->subscribed())
                <div class="mt-6">
                    <a class="px-6 py-3 bg-indigo-500 rounded text-white" href="{{ route('stripe.portal') }}">
                        {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                    </a>
                </div>
                @endif
                <div id="error-message" class="hidden p-2 mt-4 bg-pink-100"></div>

                @foreach ($plans as $planName => $planConfig)
                <div class="mt-4">
                    <h2>
                        {{ __('stripe.'.$planName) }}
                    <h2>
                    <div>
                        {{ \Laravel\Cashier\Cashier::formatAmount($planConfig['price'], null, app()->getLocale()) }}
                        @if(empty($planConfig['price_id']))
                        *
                        @endif
                        @if($planConfig['price'])
                        {{ __('stripe.price') }}
                        @endif
                    </div>
                    <p>
                        {!! __('stripe.'.$planName.'_description') !!}
                    </p>
                    <ul>
                        @foreach ($planConfig['features'] as $featureName => $feature)
                        <li>
                            {{ $feature }}
                        </li>
                        @endforeach
                        @if(!empty($planConfig['demo']))
                        <li>
                            <a class="px-6 py-3 bg-indigo-500 rounded text-white" href="https://www.witty.works/demo">
                                {{ __('stripe.schedule_demo') }}
                            </a>
                        </li>
                        @elseif(empty($team))
                        <li>
                            <a class="px-6 py-3 bg-indigo-500 rounded text-white" href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login']) }}">
                                {{ __('stripe.register_now') }}
                            </a>
                        </li>
                        @elseif ($team->planId() === $planName)
                        <li>
                            {{ __('stripe.current_plan') }}
                        </li>
                        @elseif(!empty($planConfig['checkout']))
                        <li>
                            {{ $planConfig['checkout']->button(__('stripe.checkout')) }}
                        </li>
                        @endif
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>