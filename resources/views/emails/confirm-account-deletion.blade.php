@component('mail::message')
{{ __('content.delete_account_email_intro') }}

@component('mail::button', ['url' => $url])
{{ __('content.delete_account') }}
@endcomponent

{{ __('content.delete_account_email_validity', ['minutes' => $minutes]) }}
@endcomponent
