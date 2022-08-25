<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page">
            @include('partials.extension-check')
            @auth
            @include('partials.onboarding')
            @else
            <x-jet-welcome />
            @endauth
        </div>
    </div>
</x-app-layout>

