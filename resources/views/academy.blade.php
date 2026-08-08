<x-app-layout :pagetitle="__('content.academy')">
    <div class="wittyworks-navigation-wrapper">
        @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page-academy lg:ml-20">
            @include('partials.banners')
            <h1 class="ibarra-sub-title-h1 margin-top">
                {{ __('content.academy') }}
            </h1>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" label="{{ __('content.academy_video_1_label') }}" />
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" label="{{ __('content.academy_video_2_label') }}" />
            </div>
        </div>
    </div>
</x-app-layout>
