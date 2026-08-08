<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('content.email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('content.password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror />
            </div>

            <div class="block mt-4 flex items-center">
                <x-checkbox id="remember_me" name="remember" label="{{ __('content.remember_me') }}" />
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('content.forgot_your_password') }}
                    </a>
                @endif

                <x-button class="ml-3">
                    {{ __('content.log_in') }}
                </x-button>
            </div>
            
            <div class="flex items-center justify-center mt-4">
                <span class="text-sm text-gray-600">{{ __('content.not_yet_registered') }}</span>
                <a class="ml-2 underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                    {{ __('content.register') }}
                </a>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
