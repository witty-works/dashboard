<x-plain-layout :pagetitle="__('content.office_register')">
    <div class="office-login-wrapper">
        <div class="office-login-image" style="background-image: url({{ asset('witty-background.png') }});"></div>
        <form class="office-login-form" action="{{ $action }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />
            <input type="hidden" name="confirm" value="1" />

            @if ($signedInAs)
                <p>{{ __('content.office_login_signed_in_as', ['email' => $signedInAs]) }}</p>
                <h1>{{ __('content.office_login_switch_to', ['email' => $email]) }}</h1>
            @else
                <h1>{{ __('content.office_login_continue_as', ['email' => $email]) }}</h1>
            @endif

            <p style="margin-top: 1em">{{ __('content.office_login_continue_as_description') }}</p>

            <div style="margin-top: 2em">
                <x-button>
                    {{ __('content.office_login_continue') }}
                </x-button>
            </div>
        </form>
    </div>
</x-plain-layout>
