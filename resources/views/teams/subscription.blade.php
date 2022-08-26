<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription">
                @livewire('teams.plan-summary', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
