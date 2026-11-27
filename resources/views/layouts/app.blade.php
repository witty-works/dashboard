<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pagetitle ? str_replace('&shy;', '', $pagetitle) : config('app.name') }}</title>

        <!-- Fonts -->
        @googlefonts('lato')
        @googlefonts('ibarra')
        @googlefonts('roboto')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        <link rel="icon" type="image/webp" href="{{ URL::asset('/witty-icon-color-inverted@2x-1.webp') }}"/>
        @livewireStyles

        <!-- Scripts -->
        @include('partials/sentry')
        @php
            $user = Auth::user();
        @endphp
    </head>

    <body class="font-sans antialiased">
        {{-- The first thing a keyboard reaches, at every screen width (WCAG 2.4.1). --}}
        <a class="skip-link" href="#maincontent">{{ __('content.skip_to_main_content') }}</a>

        <x-banner />

        <div class="min-h-screen bg-gray-100">
            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            @isset($navigation)
                {{-- Outside <main>, so that the navigation landmark is not part of the main content. --}}
                <div class="wittyworks-navigation-wrapper">
                    {{ $navigation }}
                </div>

                <main class="wittyworks-page-wrapper" id="maincontent">
                    {{ $slot }}
                </main>
            @else
                {{-- Pages without the navigation slot. The analytics page still
                     puts the navigation and its own #maincontent inside. --}}
                <main @unless (str_contains((string) $slot, 'id="maincontent"')) id="maincontent" @endunless>
                    {{ $slot }}
                </main>
            @endisset

            @include('partials.footer')
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>
