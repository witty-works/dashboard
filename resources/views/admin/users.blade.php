<x-app-layout :pagetitle="__('content.admin_users_title')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('content.admin_users_title') }}</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        @include('admin.nav')
        @livewire('admin.users')
    </div>
</x-app-layout>
