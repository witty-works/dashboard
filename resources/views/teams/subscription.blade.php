<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription">
                @livewire('teams.plan-summary', ['team' => $team])

                <x-jet-section-border />

                @livewire('teams.update-team-name-form', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
