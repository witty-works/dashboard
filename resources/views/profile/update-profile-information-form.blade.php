<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('content.profile_information') }}
    </x-slot>

    <x-slot name="description">
        {!! __('content.update_your_account_profile', ['profile_url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile'])]) !!}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden"
                            wire:model="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />

                <x-jet-label for="photo" value="{{ __('content.photo') }}" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview" style="display: none;">
                    <span class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                          x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-jet-secondary-button class="mt-2 mr-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('content.select_a_new_photo') }}
                </x-jet-secondary-button>

                @if ($this->user->profile_photo_path)
                    <x-jet-secondary-button type="button" class="mt-2" wire:click="deleteProfilePhoto">
                        {{ __('content.remove_photo') }}
                    </x-jet-secondary-button>
                @endif

                <x-jet-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('content.name') }}" />
            {{ $state['name'] }}
        </div>

        <!-- Email -->
        <div class="col-span-6 sm:col-span-4">
            <x-jet-label for="email" value="{{ __('content.email') }}" />
            {{ $state['email'] }}
        </div>

        @php
            $user = Auth::user();
            $team = $user->currentTeam;
        @endphp

        <div class="mt-5 col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.plan_name') }}" />
            <div>
                {{ $user->subscribed() ? $user->subscription()->planName() : __('stripe.witty_free') }}
            </div>

            @if($team && $user->ownsTeam($team))
                <a href="{{ route('stripe.portal') }}">
                    @if($team->subscribed())
                    {{ $team->subscription()->isPaidByInvoice() ? __('stripe.contact_sales') : __('stripe.billing') }}
                    @else
                    {{ __('teams.upgrade') }}
                    @endif
                </a>
            @endif
        </div>

        <div class="mt-5 col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.team_owner') }}" />
                {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
        </div>

        <div class="mt-5 col-span-6 sm:col-span-4">
            {{ __('teams.what_is_included') }}
        </div>

        <div class="mt-5 col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.term_replacements') }}" />
                {{ __('teams.total_of_max_used', ['total' => $user->getTotalTermReplacementsCount(), 'max_count' => $user->getTermReplacementsCount()]) }}
        </div>

        <div class="mt-5 col-span-6 sm:col-span-4">
            <x-jet-label for="name" value="{{ __('teams.false_positives') }}" />
                {{ __('teams.total_of_max_used', ['total' => $user->getTotalFalsePositivesCount(), 'max_count' => $user->getFalsePositivesCount()]) }}
        </div>
    </x-slot>
</x-jet-form-section>
