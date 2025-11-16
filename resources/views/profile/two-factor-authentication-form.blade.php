<div>
    <h2>{{ __('content.two_factor_authentication') }}</h2>
    <p>{{ __('content.manage_two_factor_authentication') }}</p>
    
    <div class="mt-4">
        @if ($this->enabled)
            <div class="mb-4 text-sm text-gray-600">
                {{ __('content.two_factor_enabled') }}
            </div>

            @if ($showingConfirmation)
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('content.two_factor_finish_setup') }}
                </div>

                <div class="mb-4">
                    <x-label for="code" value="{{ __('content.confirmation_code') }}" />
                    <x-input id="code" type="text" inputmode="numeric" name="code" class="mt-1 block w-1/2" wire:model.defer="code" autofocus autocomplete="one-time-code" />
                </div>
            @endif

            <div class="mt-4">
                <x-button wire:click="showRecoveryCodes">
                    {{ __('content.show_recovery_codes') }}
                </x-button>
            </div>
        @else
            <div class="mb-4 text-sm text-gray-600">
                {{ __('content.two_factor_disabled') }}
            </div>
        @endif

        <div class="mt-4">
            @if (! $this->enabled)
                <x-button wire:click="enableTwoFactorAuthentication">
                    {{ __('content.enable_two_factor') }}
                </x-button>
            @else
                <x-danger-button wire:click="disableTwoFactorAuthentication">
                    {{ __('content.disable_two_factor') }}
                </x-danger-button>
            @endif
        </div>
    </div>
</div>
    
</div>
