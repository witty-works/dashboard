<x-app-layout :pagetitle="__('content.api_keys_title')">
    <x-slot name="navigation">
        @livewire('navigation-menu')
    </x-slot>

    <div class="wittyworks-page lg:ml-20">
        <h1 class="ibarra-sub-title-h1 margin-top">{{ __('content.api_keys_title') }}</h1>
        @include('partials.banners')

        <p class="mb-6 max-w-3xl">{{ __('content.api_keys_intro') }}</p>
        @livewire('api-keys.manager')
    </div>
</x-app-layout>
