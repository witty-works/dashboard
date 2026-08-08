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
        <div class="wittyworks-form-section-wrapper">
        
        @if (Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
        <!-- Name -->
        <div class="col-span-6 sm:col-span-4 margin-bottom">
            <x-label for="name" value="{{ __('content.name') }}" />
            <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" autocomplete="name" />
            <x-input-error for="name" class="mt-2" />
        </div>
        @else
        <dl class="lato-small-text-p margin-bottom">
            <dt>{{ __('content.name') }}:</dt>
            <dd>{{ $state['name'] }}</dd>
        </dl>
        @endif

        <dl class="lato-small-text-p margin-bottom">
            <dt>{{ __('content.email') }}:</dt>
            <dd>{{ $state['email'] }}</dd>
        </dl>
        <dl class="lato-small-text-p margin-bottom">
            <dt>{{ __('content.license') }}</dt>
            <dd>{{ $user->license_team_id ? __('guidelines.yes').' ('.$user->licenseTeam->name.')' : __('guidelines.no') }}</dd>
        </dl>

        @if(!$user->has_consented_to_mailing)
            <span class="lato-paragraph-text-p">{{ __('content.mailing_consent_title') }}</span>
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

    <x-slot name="actions">
        @if (Session::get(\App\Http\Controllers\OAuthController::LOGIN_SOURCE) !== \App\Http\Controllers\OAuthController::OFFICE_PROVIDER)
        <x-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
        @endif
    </x-slot>
</x-form-section>
