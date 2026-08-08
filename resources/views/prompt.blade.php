<x-app-layout :pagetitle="__('content.manage_members')">
    <div class="wittyworks-navigation-wrapper">
    @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page lg:ml-20">
            @include('partials.banners')

            @livewire('prompt')
        </div>
    </div>
</x-app-layout>
