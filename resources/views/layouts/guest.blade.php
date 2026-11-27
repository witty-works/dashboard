<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pagetitle ?? config('app.name') }}</title>

        <!-- Fonts -->
        @googlefonts('lato')
        @googlefonts('ibarra')
        @googlefonts('roboto')

        <!-- Styles -->
        <link rel="icon" type="image/webp" href="{{ URL::asset('/witty-icon-color-inverted@2x-1.webp') }}"/>

        <!-- Scripts -->
        @include('partials/sentry')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="font-sans text-gray-900 antialiased">
            <main class="relative flex items-top justify-center min-h-screen bg-gray-100 sm:items-center py-4 sm:pt-0">
                {{ $slot }}

                @include('partials.footer')
            </main>
        </div>
        @livewireScripts
    </body>
</html>
