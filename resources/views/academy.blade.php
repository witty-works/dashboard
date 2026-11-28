<x-app-layout :pagetitle="__('content.academy')">
    <x-slot name="navigation">
        @livewire('navigation-menu')
    </x-slot>

        <div class="wittyworks-page-academy lg:ml-20">
            <h1 class="ibarra-sub-title-h1 margin-top">
                {{ __('content.academy') }}
            </h1>
            @include('partials.banners')
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" label="{{ __('content.academy_video_1_label') }}" />
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" label="{{ __('content.academy_video_2_label') }}" />
            </div>
        </div>
</x-app-layout>
