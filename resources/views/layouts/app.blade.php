<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials/gtm-header')

        <title>{{ $pagetitle ?? config('app.name') }}</title>

        <!-- Fonts -->
        @googlefonts('lato')
        @googlefonts('ibarra')
        @googlefonts('roboto')

        <!-- Styles -->
        <link rel="icon" type="image/webp" href="{{ URL::asset('/witty-icon-color-inverted@2x-1.webp') }}"/>
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        @livewireStyles

        <x-embed-styles />

        <!-- Scripts -->
        @include('partials/sentry')
        <script src="{{ mix('js/app.js') }}" defer></script>
        @php
            $user = Auth::user();
        @endphp
        @include('partials/hubspot', ['user' => $user])
    </head>
    <body class="font-sans antialiased">
        @include('partials/gtm-body')
        @livewire('onboarding', ['user' => $user])

        <x-jet-banner />

        <div>
            <!-- Page Heading -->
            @if (isset($header))
                <header>
                    {{ $header }}
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            @include('partials.footer')
        </div>

        @stack('modals')

        @livewireScripts
        @include('partials/helphero')
    </body>
</html>
