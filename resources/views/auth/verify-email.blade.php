<x-guest-layout :pagetitle="__('content.verify_email_title')">
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <h1 class="mb-4 text-2xl leading-tight">{{ __('content.verify_email_title') }}</h1>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-700" role="status">
                {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
            </div>
        @endif

        <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf

                <x-button type="submit">
                    {{ __('Resend Verification Email') }}
                </x-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout>
