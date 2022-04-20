<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('guidelines.organization_guidelines') }}
        </h2>

        {{ __('guidelines.manage_organization_guidelines_description') }}
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.language', ['team' => $team])
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.english', ['team' => $team])
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.german', ['team' => $team])
        </div>
    
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.expert-mode', ['team' => $team])
        </div>
    
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.inspirations', ['team' => $team])
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.inclusive', ['team' => $team])
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.style', ['team' => $team])
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('organization-guidelines.orthography', ['team' => $team])
        </div>
    </div>
</x-app-layout>