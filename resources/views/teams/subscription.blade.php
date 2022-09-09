<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.banners')

                @livewire('teams.plan-summary', ['team' => $team])

                <x-jet-section-border />

                @livewire('teams.update-team-name-form', ['team' => $team])
            </div>
        </div>
    </div>
</x-app-layout>
