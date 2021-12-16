@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message')
    <div class="content">
        <div class="title">Something went wrong.</div>

        @if(config('sentry.dsn') && app()->bound('sentry') && app('sentry')->getLastEventId())
            <div class="subtitle">Error ID: {{ app('sentry')->getLastEventID() }}</div>

            <script
                src="https://browser.sentry-cdn.com/6.16.1/bundle.min.js"
                integrity="sha384-WkFzsrcXKeJ3KlWNXojDiim8rplIj1RPsCbuv7dsLECoXY8C6Cx158CMgl+O+QKW"
                crossorigin="anonymous"
            ></script>

            <script>
                Sentry.init({ dsn: {!! json_encode(config('sentry.dsn')) !!} });
                Sentry.showReportDialog({
                    eventId: '{{ app('sentry')->getLastEventId() }}'
                });
            </script>
        @endif
    </div>
@endsection
