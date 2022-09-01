
<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
    <div class="wittyworks-page-wrapper">
        <div class="wittyworks-page-academy">
            @include('partials.extension-check')
            @include('partials.invite-check')
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=giQrWB9C7Xg" />
            </div>
            <div class="wittyworks-video-wrapper">
                <x-embed url="https://www.youtube.com/watch?v=L7TXSB3Me-8" />
            </div>
        </div>
    </div>
</x-app-layout>