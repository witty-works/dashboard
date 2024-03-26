<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('teams.profile') }}
    </x-slot>
    <x-slot name="description">
    </x-slot>
    <x-slot name="form">
        @php
            $user = Auth::user();
        @endphp
        <div class="wittyworks-form-section-wrapper" role="group" aria-labelledby="form-title">
        <div class="lato-small-text-p margin-bottom">
            <div>{{ __('content.name') }}:</div>
            <div>{{ $state['name'] }}</div>

            @if (Session::get('login_source') !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
            <a href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile']) }}">
                {!! __('content.update_your_account_profile', ['profile_url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile'])]) !!}
            </a>
            @endif
        </div>
        <div class="lato-small-text-p margin-bottom">
            <div>{{ __('content.email') }}:</div>
            <div>{{ $state['email'] }}</div>
        </div>
        <div class="lato-small-text-p margin-bottom">
            <div>{{ __('content.license') }}</div>
            <div>{{ $user->license_team_id ? __('guidelines.yes').' ('.$user->licenseTeam->name.')' : __('guidelines.no') }}</div>
        </div>

        @if(!$user->has_consented_to_mailing)
            <x-label id="mailingConsentLabel" for="mailing">{{ __('content.mailing_consent_title') }}</x-label>
            <div class="container-row lato-small-text-p" aria-labelledby="mailingConsentLabel">
                {{ __('content.mailing_consent_text') }}
            </div>
            <div class="container-row lato-small-text-p">
                <a class="button primary-button-purple" href="{{ route('user.mailing_consent') }}?consent=1" role="button">
                    {{ __('content.mailing_consent_button') }}
                </a>
            </div>
        @endif

    </div>
    </x-slot>
</x-form-section>
