@section('no-tracking', true)
@push('head-first')
    <script>
        // Take the token out of the address before any other script runs.
        window.officeSsoToken = new URLSearchParams(location.hash.slice(1)).get('token');
        history.replaceState(null, '', location.pathname);
    </script>
@endpush
<x-plain-layout :pagetitle="__('content.office_register')">
    <div class="office-login-wrapper">
        <div class="office-login-image" style="background-image: url({{ asset('witty-background.png') }});"></div>
        {{-- The add-in puts its token in the fragment, which never reaches the
             server; it is posted from here. --}}
        <form id="office-sso-form" class="office-login-form" action="{{ $action }}" method="POST">
            @csrf
            <input type="hidden" name="token" id="office-sso-token" value="" />

            <p id="office-sso-status">{{ __('content.office_sso_signing_in') }}</p>
            <noscript><p>{{ __('content.office_sso_needs_javascript') }}</p></noscript>
        </form>
    </div>

    <script>
        (function () {
            var token = window.officeSsoToken;
            window.officeSsoToken = null;
            if (!token) {
                document.getElementById('office-sso-status').textContent = @json(__('content.office_sso_open_from_word'));
                return;
            }
            document.getElementById('office-sso-token').value = token;
            document.getElementById('office-sso-form').submit();
        })();
    </script>
</x-plain-layout>
