<x-plain-layout :pagetitle="__('content.delete_account')">
    <div class="office-login-wrapper">
        <form class="office-login-form" action="{{ $action }}" method="POST">
            @csrf
            <h1>{{ __('content.delete_account') }}</h1>

            @if (! empty($reason))
                <p role="alert" style="margin-top: 1em">{{ $reason }}</p>
            @else
                <p style="margin-top: 1em">{{ __('content.once_your_account_is_deleted') }}</p>
            @endif

            <div style="margin-top: 2em">
                <x-danger-button type="submit">
                    {{ __('content.delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </div>
</x-plain-layout>
