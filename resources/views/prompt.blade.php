<x-app-layout :pagetitle="__('content.manage_members')">
    <nav class="wittyworks-navigation-wrapper" id="maincontent" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </nav>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners')

            @livewire('prompt')
        </div>
    </div>
</x-app-layout>
