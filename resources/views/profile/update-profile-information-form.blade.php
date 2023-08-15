<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        <h2 id="profile-title">{{ __('teams.profile') }}</h2>
    </x-slot>
    <x-slot name="description">
        <!-- Keep the slot in case you want to add a description later, but if you're certain it won't be used, consider removing it. -->
    </x-slot>
    <x-slot name="form">
        <div class="wittyworks-form-section-wrapper" role="group" aria-labelledby="profile-title">
        <!-- Name -->
        <div class="lato-small-text-p margin-bottom">
            <p>{{ __('content.name') }}: &nbsp; </p> {{ $state['name'] }}
            <a href="{{ route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile']) }}" aria-label="{{ __('content.update_your_account_profile_aria_label') }}">
                {!! __('content.update_your_account_profile', ['profile_url' => '']) !!}
            </a>
        </div>
        <!-- Email -->
        <div class="lato-small-text-p margin-bottom">
            <p>{{ __('content.email') }}: &nbsp; </p> {{ $state['email'] }}
        </div>
            
            @if(!Auth::user()->has_consented_to_mailing)
                <!-- Mailing Consent -->
                <x-jet-label id="mailingConsentLabel" for="mailing">{{ __('content.mailing_consent_title') }}</x-jet-label>
                <div class="container-row lato-small-text-p" aria-labelledby="mailingConsentLabel">
                    {{ __('content.mailing_consent_text') }}
                </div>
                <div class="container-row lato-small-text-p">
                    <a class="button primary-button-purple" href="{{ route('user.mailing_consent') }}?consent=1" role="button" aria-label="{{ __('content.mailing_consent_button_aria_label') }}">
                        {{ __('content.mailing_consent_button') }}
                    </a>
                </div>
            @endif
        </div>
    </x-slot>
</x-jet-form-section>
