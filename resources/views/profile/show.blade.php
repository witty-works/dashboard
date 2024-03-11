<x-app-layout :pagetitle="__('content.manage_account')">
    <div class="wittyworks-navigation-wrapper" role="navigation">
        @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent" role="main">
        <div class="wittyworks-page-subscription lg:ml-20">
            @include('partials.banners')
            <h1 class="ibarra-sub-title-h1 margin-top" role="heading" aria-level="1">
                {{ __('content.manage_account') }}
            </h1>
            
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @cannot('update', $user->currentTeam)
            <div class="py-10">
                @livewire('teams.plan-summary', ['team' => $user->currentTeam])
                @livewire('teams.team-member-manager-help-hero', ['team' => Auth::user()->currentTeam])
            </div>
            @endcannot
        </div>
    </div>
</x-app-layout>
