<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('errors.page_not_found') }}
        </h2>
    </x-slot>
    <div class="pt-4 bg-gray-100">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <p>{{ __('errors.errors_text') }}</p>
            <a href="/" class="btn btn-primary">{{ __('errors.back_button') }}</a>
        </div>
    </div>
</x-app-layout>