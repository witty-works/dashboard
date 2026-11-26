<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Runs before anything else on the page, e.g. to take a credential out of the address. --}}
        @stack('head-first')

        {{-- Pages that handle a credential leave out tag manager and analytics. --}}
        @hasSection('no-tracking')
        @else
            @include('partials/gtm-header')
        @endif

        <title>{{ $pagetitle ?? config('app.name') }}</title>

        <!-- Fonts -->
        @googlefonts('lato')
        @googlefonts('ibarra')
        @googlefonts('roboto')

        <!-- Styles -->
        <link rel="icon" type="image/webp" href="{{ URL::asset('/witty-icon-color-inverted@2x-1.webp') }}"/>
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        @include('partials/sentry')
        <script src="{{ mix('js/app.js') }}" defer></script>
        @include('partials/detect_browser')

    </head>
    <body class="font-sans antialiased">
        @hasSection('no-tracking')
        @else
            @include('partials/gtm-body')
        @endif
        <div class="font-sans text-gray-900 antialiased">
            <main class="relative flex items-top justify-center min-h-screen bg-gray-100 sm:items-center py-4 sm:pt-0">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
