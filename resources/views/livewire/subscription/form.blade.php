<x-form-section submit="storeSubscription">

    <x-slot name="title">
        <h2 id="subscriptions">Create subscription</h2>
    </x-slot>

    <x-slot name="description">
        <p aria-describedby="subscriptions">
            
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="w-full col-span-6 sm:col-span-4">
            <x-input id="subscription_id"
                    type="hidden"
                    wire:model="subscription_id" />

            <x-label for="team_id" value="Team ID *" />
            <x-input id="team_id"
                type="text"
                class="mt-1 block w-full"
                wire:model="team_id"
                autocomplete="team_id"
                :disabled="$subscription_id" />
            <x-input-error for="team_id" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="stripe_price" value="Plan *" />
            <x-select id="stripe_price"
                :options="$prices"
                class="mt-1 block w-full"
                wire:model="stripe_price" />

            <x-input-error for="stripe_price" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="stripe_status" value="Status *" />
            <x-select id="stripe_status"
                      :options="$status"
                      class="mt-1 block w-full"
                      wire:model="stripe_status" />
            <x-input-error for="stripe_status" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label id="quantity_label" for="quantity" value="Quantity *" />
            <x-input id="quantity"
                type="text"
                class="mt-1 block w-full textarea-as-input"
                wire:model="quantity" />
            <x-input-error for="quantity" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="starts_at" value="Starts at *" />
            <x-input id="starts_at"
                type="date"
                class="mt-1 block w-full"
                wire:model="starts_at" />
            <x-input-error for="starts_at" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="ends_at" value="Ends at" />
            <x-input id="ends_at"
                type="date"
                class="mt-1 block w-full"
                wire:model="ends_at" />
            <x-input-error for="ends_at" class="mt-2" />
        </div>

        <div class="w-full col-span-6 sm:col-span-4 mt-5">
            <x-label for="trial_ends_at" value="Trial ends at" />
            <x-input id="trial_ends_at"
                type="date"
                class="mt-1 block w-full"
                wire:model="trial_ends_at" />
            <x-input-error for="trial_ends_at" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        @include('partials/save_cancel_action')
    </x-slot>
</x-form-section>