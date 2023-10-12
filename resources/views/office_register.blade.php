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

            <h2>{{ $name }} {{ $email }}</h2>
        
            <div style="margin-top: 2em">
                <label class="switch">
                    <input
                        type="hidden"
                        name="has_consented_to_terms_of_service"
                        value="0"
                    />
                    <input
                        type="checkbox"
                        class="guidelines-form-section-toggle"
                        name="has_consented_to_terms_of_service"
                        value="1"
                        @if(old('has_consented_to_terms_of_service'))
                        checked="checked"
                        @endif
                    />
                    <span class="slider round"></span>
                </label>

                <x-jet-label for="has_consented_to_terms_of_service" value="{!! __('content.has_consented_to_terms_of_service') !!}" />

                @if($errors->hasBag('default') && $errors->getBag('default')->getMessages())
                <p class="mt-2 text-sm text-red-600">{{ __('content.you_must_consent') }}</p>
                @endif
            </div>

            <div>
                <label class="switch">
                    <input
                        type="hidden"
                        name="has_consented_to_mailing"
                        value="0"
                    />
                    <input
                        type="checkbox"
                        class="guidelines-form-section-toggle"
                        name="has_consented_to_mailing"
                        value="1"
                        @if(old('has_consented_to_mailing'))
                        checked="checked"
                        @endif
                    />
                    <span class="slider round"></span>
                </label>

                <x-jet-label for="has_consented_to_mailing" value="{!! __('content.has_consented_to_mailing') !!}" />

                <x-jet-input-error for="has_consented_to_mailing" class="mt-2" />
            </div>

            <div>
                <x-jet-button>
                    {{ __('content.register') }}
                </x-jet-button>
            </div>

            <div style="margin-top: 2em">
                <h3>{{ __('content.already_registered_with_a_different_email_title') }}</h3>

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
</x-app-layout>
