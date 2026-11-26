<x-app-layout :pagetitle="__('content.api_keys_title')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('content.api_keys_title') }}</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <p class="mb-6 max-w-3xl">{{ __('content.api_keys_intro') }}</p>
        @livewire('api-keys.manager')
    </div>
</x-app-layout>
