<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('teams.profile') }}
    </x-slot>
    <x-slot name="description"></x-slot>
    <x-slot name="form">
    <div class="wittyworks-form-section-wrapper">
        <!-- Name -->
        <x-jet-label for="name" value="{{ __('content.name') }}" />
        <div class="container-row lato-small-text-p margin-bottom">
            {{ $state['name'] }}&nbsp;
            {!! __('content.update_your_account_profile', ['profile_url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'profile'])]) !!}
        </div>
        <!-- Email -->
        <x-jet-label for="email" value="{{ __('content.email') }}"/>
        <div class="container-row lato-small-text-p margin-bottom">
            {{ $state['email'] }}
        </div>
        @if(!Auth::user()->has_consented_to_mailing)
        <x-jet-label for="mailing" value="{{ __('content.mailing_consent_title') }}"/>
        <div class="container-row lato-small-text-p">
            {{ __('content.mailing_consent_text') }}
        </div>
        <div class="container-row lato-small-text-p">
            <a class="button primary-button-purple" href="{{ route('user.mailing_consent') }}?consent=1">
                {{ __('content.mailing_consent_button') }}
            </a>
        </div>
        @endif
    </div>
</x-slot>
</x-jet-form-section>
