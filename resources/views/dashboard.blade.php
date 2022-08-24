<x-app-layout>
    <div class="wittyworks-page">
        <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
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
</x-app-layout>
