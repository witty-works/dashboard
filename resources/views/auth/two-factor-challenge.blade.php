{{-- Fortify's sign-in challenge, and the check a superadmin passes before
     /admin (TwoFactorVerificationController), which sets $action, $intro
     and $submit. --}}
<x-guest-layout :pagetitle="__('content.two_factor_authentication')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="mb-4 text-2xl leading-tight">{{ __('content.two_factor_authentication') }}</h1>

        <div x-data="{ recovery: false }">
            @isset($intro)
                <p class="mb-4 text-sm text-gray-600">{{ $intro }}</p>
            @endisset

            <div class="mb-4 text-sm text-gray-600" x-show="! recovery">
                {{ __('content.two_factor_challenge_code') }}
            </div>

            <div class="mb-4 text-sm text-gray-600" x-show="recovery" style="display: none">
                {{ __('content.two_factor_challenge_recovery') }}
            </div>

            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ $action ?? route('two-factor.login.store') }}">
                @csrf

                <div class="mt-4" x-show="! recovery">
                    <x-label for="code" value="{{ __('content.code') }}" />
                    <x-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" />
                </div>

                <div class="mt-4" x-show="recovery" style="display: none">
                    <x-label for="recovery_code" value="{{ __('content.recovery_code') }}" />
                    <x-input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="off" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
                            x-show="! recovery"
                            x-on:click="recovery = true; $nextTick(() => { $refs.code.value = ''; $refs.recovery_code.focus() })">
                        {{ __('content.use_a_recovery_code') }}
                    </button>

                    <button type="button" class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
                            style="display: none"
                            x-show="recovery"
                            x-on:click="recovery = false; $nextTick(() => { $refs.recovery_code.value = ''; $refs.code.focus() })">
                        {{ __('content.use_an_authentication_code') }}
                    </button>

                    <x-button class="ml-3">
                        {{ $submit ?? __('content.log_in') }}
                    </x-button>
                </div>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
