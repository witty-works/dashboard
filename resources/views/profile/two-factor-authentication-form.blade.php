{{-- Jetstream's TwoFactorAuthenticationForm. Two-factor sign-in takes effect
     once a code from the app is confirmed; every action asks for the password
     first (config/fortify.php). --}}
<x-action-section>
    <x-slot name="title">
        {{ __('content.two_factor_authentication') }}
    </x-slot>

    <x-slot name="description">
        {{ __('content.two_factor_description') }}
    </x-slot>

    <x-slot name="content">
        @if (\App\Support\SuperadminTwoFactor::needsSetup($this->user))
            <p role="alert" class="mb-4 max-w-xl text-sm font-semibold text-red-700">
                {{ __('content.superadmin_two_factor_notice') }}
            </p>
        @endif

        <h3 class="text-lg font-medium text-gray-900">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('content.two_factor_finish_setup') }}
                @else
                    {{ __('content.two_factor_enabled') }}
                @endif
            @else
                {{ __('content.two_factor_disabled') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600">
            <p>{{ __('content.two_factor_explanation') }}</p>
        </div>

        @if ($this->enabled)
            {{-- Only while setting up: a confirmed secret is never shown again. --}}
            @if ($showingQrCode && is_null($this->user->two_factor_confirmed_at))
                <div class="mt-4 max-w-xl text-sm text-gray-600">
                    <p class="font-semibold">{{ __('content.two_factor_scan') }}</p>
                </div>

                <div class="mt-4 p-2 inline-block bg-white">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-4 max-w-xl text-sm text-gray-600">
                    <p class="font-semibold">
                        {{ __('content.two_factor_setup_key') }}: <span class="font-mono">{{ \Laravel\Fortify\Fortify::currentEncrypter()->decrypt($this->user->two_factor_secret) }}</span>
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div class="mt-4">
                        <x-label for="code" value="{{ __('content.code') }}" />
                        <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                            wire:model="code"
                            wire:keydown.enter="confirmTwoFactorAuthentication" />
                        <x-input-error for="code" class="mt-2" />
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="mt-4 max-w-xl text-sm text-gray-600">
                    <p class="font-semibold">{{ __('content.store_recovery_codes') }}</p>
                </div>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                    @foreach ($this->user->recoveryCodes() as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" wire:loading.attr="disabled">
                        {{ __('content.enable') }}
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button class="mr-3">
                            {{ __('content.regenerate_recovery_codes') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" class="mr-3" wire:loading.attr="disabled">
                            {{ __('content.confirm') }}
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button class="mr-3">
                            {{ __('content.show_recovery_codes') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-secondary-button wire:loading.attr="disabled">
                            {{ __('content.cancel') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            {{ __('content.disable') }}
                        </x-danger-button>
                    </x-confirms-password>
                @endif
            @endif
        </div>
    </x-slot>
</x-action-section>
