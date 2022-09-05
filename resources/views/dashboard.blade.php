<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.extension-check')
            @include('partials.invite-check')
            @include('partials.invitations')
            <x-jet-welcome />
        </div>
    </div>
</x-app-layout>

