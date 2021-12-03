<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('rules.false_positives_list') }}
        </h2>
    </x-slot>

    @can('update', Auth::user()->currentTeam)
    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('false-positive.form', ['team' => $team])
        </div>
    </div>
    @endcan

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('false-positive.show', ['team' => $team])
        </div>
    </div>
</x-app-layout>