<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.team_settings') }}
        </h2>

        @if (!Auth::user()->hasTeamPermission($team, 'update'))
        {!! __('content.making_changes_requires_admin_rights', ['email' => $team->owner->email]) !!}
        @endif
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('teams.update-team-name-form', ['team' => $team])

            @livewire('teams.team-member-manager', ['team' => $team])

            @if (Gate::check('delete', $team) && ! $team->personal_team)
                <x-jet-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('teams.delete-team-form', ['team' => $team])
                </div>
            @endif

            @livewire('teams.store-context', ['team' => $team])
        </div>
    </div>
</x-app-layout>
