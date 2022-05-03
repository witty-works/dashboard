<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('partials/gtm-header')

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="icon" type="image/webp" href="{{ URL::asset('/witty-icon-color-inverted@2x-1.webp') }}"/>
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <!-- Scripts -->
        @include('partials/sentry')        
        <script src="{{ mix('js/app.js') }}" defer></script>
        @include('partials/posthog')
    </head>
    <body>
        @include('partials/gtm-body')
        <div class="font-sans text-gray-900 antialiased">
            <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
                {{ $slot }}
        </div>
    </body>
</html>
