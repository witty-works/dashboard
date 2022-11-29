
<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page-academy lg:ml-20">
            @include('partials.banners')
            <div class="ibarra-sub-title-h1 margin-bottom">
                {{ __('content.academy') }}
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" />
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" />
            </div>
        </div>
    </div>
</x-app-layout>