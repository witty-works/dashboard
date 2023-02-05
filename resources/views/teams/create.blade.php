<x-app-layout :pagetitle="__('content.create_team')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.create_team') }}
        </h2>
    </x-slot>

    <div>
        <div class="py-10 sm:px-6 lg:px-8">
            @livewire('teams.create-team-form')
        </div>
    </div>
</x-app-layout>
