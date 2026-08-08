<x-plain-layout :pagetitle="__('content.office_register')">
    <div class="office-login-wrapper">
        <div class="office-login-image" style="background-image: url({{ asset('witty-background.png') }});"></div>
        <form class="office-login-form" action="{{ route('office_register') }}" method="POST">
            @csrf
            <input
                type="hidden"
                name="token"
                value="{{ $token }}"
            />

            <h1>{{ $name }} {{ $email }}</h1>

            <div style="margin-top: 2em">
                <div class="switch">
                    <input
                        type="hidden"
                        name="has_consented_to_terms_of_service"
                        value="0"
                    />
                    <label for="consent-checkbox" class="guidelines-form-section-toggle-label">
                        {{ __('content.consent_to_terms') }}
                    </label>
                    <input
                        type="checkbox"
                        id="consent-checkbox"
                        class="guidelines-form-section-toggle"
                        name="has_consented_to_terms_of_service"
                        value="1"
                        @if(old('has_consented_to_terms_of_service'))
                        checked="checked"
                        @endif
                        @if($errors->hasBag('default') && $errors->getBag('default')->getMessages())
                        aria-invalid="true"
                        aria-describedby="consent-error"
                        @endif
                    />
                    <span class="slider round"></span>
                </div>

                <span class="lato-paragraph-text-p">{!! __('content.has_consented_to_terms_of_service') !!}</span>

                @if($errors->hasBag('default') && $errors->getBag('default')->getMessages())
                <p id="consent-error" class="mt-2 text-sm text-red-600" role="alert">{{ __('content.you_must_consent') }}</p>
                @endif
            </div>

            <div>
                <div class="switch">
                    <input
                        type="hidden"
                        name="has_consented_to_mailing"
                        value="0"
                    />
                    <label for="consent-mailing-checkbox" class="guidelines-form-section-toggle-label">
                        {{ __('content.consent_to_mailing') }}
                    </label>
                    <input
                        type="checkbox"
                        id="consent-mailing-checkbox"
                        class="guidelines-form-section-toggle"
                        name="has_consented_to_mailing"
                        value="1"
                        @if(old('has_consented_to_mailing'))
                        checked="checked"
                        @endif
                        @error('has_consented_to_mailing')
                        aria-invalid="true"
                        aria-describedby="has_consented_to_mailing-error"
                        @enderror
                    />
                    <span class="slider round"></span>
                </div>

                <span class="lato-paragraph-text-p">{!! __('content.has_consented_to_mailing') !!}</span>

                <x-input-error for="has_consented_to_mailing" class="mt-2" />
            </div>

            <div>
                <x-button>
                    {{ __('content.register') }}
                </x-button>
            </div>

            <div style="margin-top: 2em">
                <h2>{{ __('content.already_registered_with_a_different_email_title') }}</h2>

                <p>{{ __('content.already_registered_with_a_different_email_description') }}</p>
            </div>

            <div style="margin-top: 2em">
                <h2>{{ __('content.office_register_title') }}</h2>
                <p>
                    {!! __('content.office_register_description') !!}
                </p>
                <p>
                    {!! __('content.office_register_summary') !!}
                </p>
        
                {!! __('content.office_register_further_reading') !!}
            </div>
        </form>
    </div>
</x-plain-layout>
