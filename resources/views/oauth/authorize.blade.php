{{--
    OAuth consent screen.

    Passport 13 ships no views of its own and resolves
    Contracts\AuthorizationViewResponse when building AuthorizationController,
    so this has to exist even though the browser extension never reaches it —
    that client is first-party and skips authorization (see App\Models\OAuthClient).
    Without it every request to /oauth/authorize fails with
    "Target [...AuthorizationViewResponse] is not instantiable".

    It is a real consent screen rather than a stub so that the first non-first-party
    client to appear gets a working prompt instead of a 500.
--}}
<x-app-layout :pagetitle="__('content.authorize_title')">
    <x-slot name="header">
        <h2>{{ __('content.authorize_title') }}</h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <p class="mb-4">
            {{ __('content.authorize_intro', ['client' => $client->name]) }}
        </p>

        @if (count($scopes) > 0)
            <ul class="list-disc list-inside mb-4">
                @foreach ($scopes as $scope)
                    <li>{{ $scope->description }}</li>
                @endforeach
            </ul>
        @endif

        <div class="flex gap-3">
            <form method="post" action="{{ route('passport.authorizations.approve') }}">
                @csrf
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn btn-primary">
                    {{ __('content.authorize_approve') }}
                </button>
            </form>

            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="state" value="{{ $request->state }}">
                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                <button type="submit" class="btn">
                    {{ __('content.authorize_deny') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
