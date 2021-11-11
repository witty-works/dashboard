<x-jet-action-section>
    <x-slot name="title">
        {{ __('content.connected_accounts') }}
    </x-slot>

    <x-slot name="description">
        {{ __('content.manage_and_remove_your_connect_accounts') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-gray-900">
            @if (count($this->accounts) == 0)
                {{ __('content.you_have_no_connected_accounts') }}
            @else
                {{ __('content.your_connected_accounts') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600">
            {{ __('content.you_are_free_to_connect_any_scocial_accounts') }}
        </div>

        <div class="mt-5 space-y-6">
            @foreach ($this->providers as $provider)
                @php
                    $account = null;
                    $account = $this->accounts->where('provider', $provider)->first();
                @endphp

                <x-connected-account provider="{{ $provider }}" created-at="{{ $account->created_at ?? null }}">
                    <x-slot name="action">
                        @if (! is_null($account))
                            <div class="flex items-center space-x-6">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos() && ! is_null($account->avatar_path))
                                    <button class="cursor-pointer ml-6 text-sm text-gray-500 focus:outline-none" wire:click="setAvatarAsProfilePhoto({{ $account->id }})">
                                        {{ __('content.use_avatar_as_profile_photo') }}
                                    </button>
                                @endif

                                @if (($this->accounts->count() > 1 || ! is_null($this->user->password)))
                                    <x-jet-danger-button wire:click="confirmRemove({{ $account->id }})" wire:loading.attr="disabled">
                                        {{ __('content.remove') }}
                                    </x-jet-danger-button>
                                @endif
                            </div>
                        @else
                            <x-action-link href="{{ route('oauth.redirect', ['provider' => $provider]) }}">
                                {{ __('content.connect') }}
                            </x-action-link>
                        @endif
                    </x-slot>

                </x-connected-account>
            @endforeach
        </div>

        <!-- Logout Other Devices Confirmation Modal -->
        <x-jet-dialog-modal wire:model="confirmingRemove">
            <x-slot name="title">
                {{ __('content.remove_connected_account') }}
            </x-slot>

            <x-slot name="content">
                {{ __('content.please_confirm_your_removal') }}
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$toggle('confirmingRemove')" wire:loading.attr="disabled">
                    {{ __('content.cancel') }}
                </x-jet-secondary-button>

                <x-jet-danger-button class="ml-2" wire:click="removeConnectedAccount({{ $this->selectedAccountId }})" wire:loading.attr="disabled">
                    {{ __('content.remove_connected_account') }}
                </x-jet-danger-button>
            </x-slot>
        </x-jet-dialog-modal>
    </x-slot>
</x-jet-action-section>
