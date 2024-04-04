<x-list-section>
    @if($list->count())
    <x-slot name="title">
        Subcriptions
    </x-slot>

    <x-slot name="description">
        
    </x-slot>

    <x-slot name="list">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="py-2 lato-paragraph-text-p">Team Name</th>
                    <th class="py-2 lato-paragraph-text-p">Team Owner</th>
                    <th class="py-2 lato-paragraph-text-p">Quantity</th>
                    <th class="py-2 lato-paragraph-text-p">Plan</th>
                    <th class="py-2 lato-paragraph-text-p">Status</th>
                    <th class="py-2 lato-paragraph-text-p">Starts at</th>
                    <th class="py-2 lato-paragraph-text-p">Ends at</th>
                    <th class="py-2 lato-paragraph-text-p">Trail ends at</th>
                    <th class="py-2 lato-paragraph-text-p">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($list as $subscription)
            <tr @if($loop->even)class="bg-grey"@endif>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->owner->name }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p"><a href="{{ route('impersonate', $subscription->owner->owner->id) }}">{{ $subscription->owner->owner->email }}</a></td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->quantity }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">
                    @if (empty($stripe_prices[$subscription->stripe_price]))
                    witty_{{ $subscription->stripe_price }}
                    @else
                    {{ $stripe_prices[$subscription->stripe_price] }}
                    @endif
                </td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->stripe_status }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->starts_at ? $subscription->starts_at->format('Y-m-d') : 'none' }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->ends_at ? $subscription->ends_at->format('Y-m-d') : 'none' }}</td>
                <td class="border px-4 py-2 text-left lato-paragraph-text-p">{{ $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d') : 'none' }}</td>
                <td class="border px-4 py-2 text-center container-row">
                    @if (empty($stripe_prices[$subscription->stripe_price]))
                    <button onclick="document.getElementById('organization_subscriptions')?.scrollIntoView({behavior: 'smooth'});" wire:click="editSubscription({{ $subscription->id }})" class="button primary-button-red ">
                        Edit
                    </button>
                    @else
                    <a href="{{ config('stripe.subscription_url') }}/{{ $subscription->stripe_id }}" target="_new" class="button primary-button-red ">
                        Go to Stripe
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </x-slot>
    @endif
</x-list-section>