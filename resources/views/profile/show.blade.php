<x-app-layout :pagetitle="__('content.manage_account')">
    <div class="wittyworks-navigation-wrapper">
    @livewire('navigation-menu')
    </div>
    <div class="wittyworks-page-wrapper" id="maincontent">
        <div class="wittyworks-page-subscription lg:ml-20">
            @include('partials.banners')
            <h1 class="ibarra-sub-title-h1 margin-top">
                {{ __('content.manage_account') }}
            </h1>
            
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')
            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()) && Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
                <x-section-border />

                @livewire('profile.update-password-form')
            @endif

            {{-- It applies to signing in with a password, which Word add-in
                 users do not do; superadmins need it wherever they are. --}}
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()
                && (Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER
                    || \App\Support\SuperadminTwoFactor::applies($user)))
                <x-section-border />

                @livewire('profile.two-factor-authentication-form')
            @endif

            @if ($user->currentTeam && Gate::denies('update', $user->currentTeam))
            <div class="py-10">
                @livewire('teams.team-member-manager-help-hero', ['team' => Auth::user()->currentTeam])
            </div>
            @endif

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <x-section-border />

                @livewire('profile.delete-account-form')
            @endif
        </div>
    </div>
</x-app-layout>
