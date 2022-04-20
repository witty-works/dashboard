<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.browser_login') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                @auth
                    @if(env('APP_DEBUG'))
                    <ul>
                        <li>email: {{ $email }}</li>
                        <li>access_token: {{ $access_token }}</li>
                        <li>refresh_token: {{ $refresh_token }}</li>
                    </ul>
                    @else
                    {{ __('content.login_failed') }}
                    @endif
                @else
                <a href="{{ route('browser_login') }}">
                    {{ __('content.log_in') }}
                </a>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>