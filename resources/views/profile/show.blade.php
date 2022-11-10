<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page-subscription lg:ml-20">
                @include('partials.banners')
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    @livewire('profile.update-profile-information-form')
                @endif

                @cannot('update', Auth::user()->currentTeam)
                <div class="py-10">
                    @livewire('teams.plan-summary', ['team' => Auth::user()->currentTeam])

                    @livewire('teams.team-member-manager', ['team' => Auth::user()->currentTeam])
                </div>
                @endcannot
            </div>
        </div>
    </div>
</x-app-layout>
