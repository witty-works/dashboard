<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div>
                @include('partials.extension-check')
                @auth
                @include('partials.onboarding')
                @include('partials.quicklinks')
                <div class="pt-6">
                    <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" />
                </div>

                <div class="pt-6">
                    <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" />
                </div>
                @else
                <x-jet-welcome />
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>
