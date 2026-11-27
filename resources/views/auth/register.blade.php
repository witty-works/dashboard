<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('content.name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" error="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('content.email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" error="email" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('content.password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" error="password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('content.confirm_password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" error="password_confirmation" />
            </div>

            {{-- Only for the documents the deployment provides (PRIVACY_POLICY_FILE, TERMS_OF_SERVICE_FILE). --}}
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature() && App\Support\LegalDocument::anyProvided())
                <div class="mt-4">
                    <div class="flex items-center">
                        <x-checkbox name="terms" id="terms" label="" aria-label="{{ trim(view('partials.legal-consent', ['links' => false])->render()) }}" required />

                        <div class="ml-2">
                            @include('partials.legal-consent')
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('content.already_registered') }}
                </a>

                <x-button class="ml-4">
                    {{ __('content.register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
