<x-app-layout :pagetitle="__('content.academy')">
    <nav class="wittyworks-navigation-wrapper" aria-label="Main Navigation">
        @livewire('navigation-menu')
    </nav>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page-academy lg:ml-20">
            @include('partials.banners')
            <h1 class="ibarra-sub-title-h1 margin-top">
                {{ __('content.academy') }}
            </h1>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" />
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" />
            </div>
        </div>
    </div>
</x-app-layout>
