<x-app-layout :pagetitle="__('content.manage_account')">
    <nav class="wittyworks-navigation-wrapper" aria-label="Main Navigation">
    @livewire('navigation-menu')
    </nav>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page-subscription lg:ml-20">
            @include('partials.banners')
            <h1 class="ibarra-sub-title-h1 margin-top" role="heading" aria-level="1">
                {{ __('content.manage_account') }}
            </h1>
            
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()) && Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
                <x-section-border />

                @livewire('profile.update-password-form')
            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <x-section-border />

                @livewire('profile.two-factor-authentication-form')
            @endif

            @cannot('update', $user->currentTeam)
            <div class="py-10">
                @livewire('teams.team-member-manager-help-hero', ['team' => Auth::user()->currentTeam])
            </div>
            @endcannot
        </div>
    </div>
</x-app-layout>
