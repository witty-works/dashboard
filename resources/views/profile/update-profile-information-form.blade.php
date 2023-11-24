<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('teams.profile') }}
    </x-slot>
    <x-slot name="description">
    </x-slot>
    <x-slot name="form">
        <div class="wittyworks-form-section-wrapper" role="group" aria-labelledby="form-title">
        <!-- Name -->
        <div class="lato-small-text-p margin-bottom">
            <div>{{ __('content.name') }}:</div>
            <div>{{ $state['name'] }}</div>

            @if (Session::get('login_source') !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
            <a href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile']) }}">
                {!! __('content.update_your_account_profile', ['profile_url' => '']) !!}
            </a>
            @endif
        </div>
        <!-- Email -->
        <div class="lato-small-text-p margin-bottom">
            <div>{{ __('content.email') }}:</div>
            <div>{{ $state['email'] }}</div>
        </div>
            
            @if(!Auth::user()->has_consented_to_mailing)
                <!-- Mailing Consent -->
                <x-jet-label id="mailingConsentLabel" for="mailing">{{ __('content.mailing_consent_title') }}</x-jet-label>
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
</x-jet-form-section>
